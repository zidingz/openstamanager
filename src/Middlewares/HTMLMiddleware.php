<?php

namespace Middlewares;

use HTMLBuilder\HTMLBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Stream;

/**
 * Middlware per la gestione della lingua del progetto.
 *
 * @since 2.5
 */
class HTMLMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Versione e revisione
        $this->addVariable('version', \Update::getVersion());
        $this->addVariable('revision', \Update::getRevision());

        // Gestione HTML personalizzato
        $response = $handler->handle($request);
        $html = $response->getBody();

        $id_module = \Modules\Module::getCurrent()['id'];
        $html = str_replace('$id_module$', $id_module, $html);
        //$html = str_replace('$id_plugin$', $id_plugin, $html);
        //$html = str_replace('$id_record$', $id_record, $html);
        $html = HTMLBuilder::replace($html);

        $stream = fopen('php://temp', 'w');
        $body = new Stream($stream);
        $body->write($html);
        $response = $response->withBody($body);

        return $response;
    }
}
