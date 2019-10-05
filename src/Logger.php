<?php

use Monolog\Handler\FilterHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;

class Logger extends Monolog\Logger
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('Logs');
        $this->pushProcessor(new Monolog\Processor\UidProcessor());
        $this->pushProcessor(new Monolog\Processor\WebProcessor());

        $handlers = [];
        // File di log di base (logs/error.log, logs/setup.log)
        $handlers[] = new StreamHandler(__DIR__.'/../logs/error.log', Monolog\Logger::ERROR);

        // File di log ordinati in base alla data
        if ($container['debug']) {
            $handlers[] = new RotatingFileHandler(__DIR__.'/../logs/error.log', 0, Monolog\Logger::ERROR);
        }

        $pattern = '[%datetime%] %channel%.%level_name%: %message% %context%'.PHP_EOL.'%extra% '.PHP_EOL;
        $monologFormatter = new Monolog\Formatter\LineFormatter($pattern);
        $monologFormatter->includeStacktraces($container['debug']);

        // Filtra gli errori per livello preciso del gestore dedicato
        foreach ($handlers as $handler) {
            $handler->setFormatter($monologFormatter);
            $this->pushHandler(new FilterHandler($handler, [$handler->getLevel()]));
        }

        // Imposta Monolog come gestore degli errori
        Monolog\ErrorHandler::register($this, [], Monolog\Logger::ERROR, Monolog\Logger::ERROR);

        register_shutdown_function([$this, 'fatalHandler']);
    }

    public function fatalHandler()
    {
        // Determine if there was an error and that is why we are about to exit.
        $error = error_get_last();

        // If there was an error then $error will be an array, otherwise null.
        if ($error['type'] === E_ERROR) {
            dd(error_get_last());
            $response = $this->container->response;
            $response = $this->container['twig']->render($response, 'errors/500.twig');

            http_response_code(500);

            echo $response->getBody()->__toString();
        }
    }

    public function logException(\Exception $exception)
    {
        $this->addError($exception->getMessage(), [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
