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

namespace Middlewares;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middlware per la gestione della lingua del progetto.
 *
 * @since 2.5
 */
class CSRFMiddleware extends Middleware
{
    protected $csrf;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $csrf = new \Slim\Csrf\Guard();
        $csrf->setPersistentTokenMode(true);

        $this->csrf = $csrf;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->csrf->__invoke($request, $handler, function ($a, $b) {
            return $a;
        });

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        $request = $result;

        // CSRF token name and value
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);

        $csrf_input = '
<input type="hidden" name="'.$nameKey.'" value="'.$name.'">
<input type="hidden" name="'.$valueKey.'" value="'.$value.'">';

        // Registrazione informazioni per i template
        $this->addVariable('csrf_input', $csrf_input);

        return $handler->handle($request);
    }
}
