<?php

namespace Controllers;

use Models\Template;
use Prints;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PrintController extends Controller
{
    public function view(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $link = urlFor('print-open', [
            'print_id' => $args['print_id'],
            'record_id' => $args['record_id'],
        ]);
        $args['link'] = $request->getUri()->getBasePath().'/assets/pdfjs/web/viewer.html?file='.$link;

        $response = $this->twig->render($response, 'uploads\frame.twig', $args);

        return $response;
    }

    public function open(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $print = Template::find($args['print_id']);
        $manager = $print->getManager($this->container, $args['record_id']);

        $pdf = $manager->render();

        $response = $response
            ->withHeader('Content-Type', 'application/pdf')
            ->write($pdf);

        return $response;
    }
}
