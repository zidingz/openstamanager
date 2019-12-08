<?php

namespace Middlewares;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware per l'implementazione del periodo temporale.
 *
 * @since 2.5
 */
class CalendarMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        // Calendario
        // Periodo di visualizzazione
        if (!empty($_GET['period_start'])) {
            $_SESSION['period_start'] = $_GET['period_start'];
            $_SESSION['period_end'] = $_GET['period_end'];
        }
        // Dal 01-01-yyy al 31-12-yyyy
        elseif (!isset($_SESSION['period_start'])) {
            $_SESSION['period_start'] = date('Y').'-01-01';
            $_SESSION['period_end'] = date('Y').'-12-31';
        }

        $this->addVariable('calendar', [
            'start' => $_SESSION['period_start'],
            'end' => $_SESSION['period_end'],
            'is_current' => ($_SESSION['period_start'] != date('Y').'-01-01' || $_SESSION['period_end'] != date('Y').'-12-31'),
        ]);

        return $handler->handle($request);
    }
}
