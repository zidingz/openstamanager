<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.n.c.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use Monolog\Handler\FilterHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use Slim\Exception\HttpSpecializedException;
use Slim\Http\Response;
use Slim\Interfaces\ErrorHandlerInterface;

class Logger extends Monolog\Logger implements ErrorHandlerInterface
{
    protected $container;

    protected $debug_handler;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('Logs');
        $this->pushProcessor(new Monolog\Processor\UidProcessor());
        $this->pushProcessor(new Monolog\Processor\WebProcessor());

        $handlers = [];
        // File di log di base (logs/error.log, logs/setup.log)
        $handlers[] = new StreamHandler(__DIR__.'/../logs/setup.log', Monolog\Logger::EMERGENCY);
        $handlers[] = new StreamHandler(__DIR__.'/../logs/error.log', Monolog\Logger::ERROR);

        // File di log ordinati in base alla data
        if ($container->get('debug')) {
            $handlers[] = new RotatingFileHandler(__DIR__.'/../logs/setup.log', 0, Monolog\Logger::EMERGENCY);
            $handlers[] = new RotatingFileHandler(__DIR__.'/../logs/error.log', 0, Monolog\Logger::ERROR);
        }

        $pattern = '[%datetime%] %channel%.%level_name%: %message% %context%'.PHP_EOL.'%extra% '.PHP_EOL;
        $monologFormatter = new Monolog\Formatter\LineFormatter($pattern);
        $monologFormatter->includeStacktraces($container->get('debug'));

        // Filtra gli errori per livello preciso del gestore dedicato
        foreach ($handlers as $handler) {
            $handler->setFormatter($monologFormatter);
            $this->pushHandler(new FilterHandler($handler, [$handler->getLevel()]));
        }

        // Imposta<ione della gestore degli errori
        Monolog\ErrorHandler::register($this, false, false, Monolog\Logger::ERROR);
        register_shutdown_function([$this, 'commonHandler']);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): \Psr\Http\Message\ResponseInterface
    {
        // Pulizia contenuti precedenti
        ob_end_clean();

        // Individuazione eccezione
        if ($exception instanceof HttpSpecializedException) {
            $status = $exception->getCode();
        } else {
            // Logging
            $this->logException($exception);

            $status = 500;
        }

        if ($this->container->get('debug') && !in_array($status, [404, 403])) {
            return $this->debug_handler->__invoke($request, $exception, true, $logErrors, $logErrorDetails);
        } else {
            // Pulizia dell'errore
            error_clear_last();

            return $this->render($status);
        }
    }

    public function setDebugHandler($handler): void
    {
        $this->debug_handler = $handler;
    }

    /**
     * Gestore per errori PHP precedenti a Slim.
     */
    public function commonHandler()
    {
        $error = error_get_last();

        // Controllo sull'ultimo errore disponibile
        if ($error['type'] === E_ERROR) {
            http_response_code(500);

            echo '<p>Errore 500: Qualcosa è andato storto più storto del previsto.</p>
<p>Per maggiori informazioni consulta i log del gestionale.</p>';
        }
    }

    /**
     * Metodo per la gestione della grafica relativa agli errori.
     */
    public function render(int $status): Response
    {
        // Visualizzazione grafica
        $response = $this->container->get('response_factory')->createResponse($status);

        return $this->container->get('twig')->render($response, 'errors/'.$status.'.twig');
    }

    /**
     * Metodo per il logging delle eccezioni.
     */
    public function logException(Throwable $exception)
    {
        $this->addRecord(self::ERROR, $exception->getMessage(), [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
