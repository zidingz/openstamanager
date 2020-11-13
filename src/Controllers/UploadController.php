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

namespace Controllers;

use DOMDocument;
use Models\Upload;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use XSLTProcessor;

class UploadController extends Controller
{
    public function view(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $file = Upload::find($args['upload_id']);

        if (empty($file)) {
            return $response;
        }

        $link = urlFor('upload-open', [
            'upload_id' => $args['upload_id'],
        ]);

        $args['file'] = $file;
        $args['link'] = $link;

        if ($file->isFatturaElettronica()) {
            $content = file_get_contents(DOCROOT.'/'.$file->filepath);

            // Individuazione stylesheet
            $default_stylesheet = 'asso-invoice';

            $name = basename($file->original_name);
            $filename = explode('.', $name)[0];
            $pieces = explode('_', $filename);
            $stylesheet = $pieces[2];

            $stylesheet = DOCROOT.'/plugins/xml/'.$stylesheet.'.xsl';
            $stylesheet = file_exists($stylesheet) ? $stylesheet : DOCROOT.'/plugins/xml/'.$default_stylesheet.'.xsl';

            // XML
            $xml = new DOMDocument();
            $xml->loadXML($content);

            // XSL
            $xsl = new DOMDocument();
            $xsl->load($stylesheet);

            // XSLT
            $xslt = new XSLTProcessor();
            $xslt->importStylesheet($xsl);

            $args['content'] = $xslt->transformToXML($xml);

            $response = $this->twig->render($response, '@resources/uploads/xml.twig', $args);
        } elseif ($file->isImage()) {
            $response = $this->twig->render($response, '@resources/uploads/img.twig', $args);
        } elseif ($file->isPDF()) {
            $args['link'] = $request->getUri()->getBasePath().'/assets/pdfjs/web/viewer.html?file='.$link;

            $response = $this->twig->render($response, '@resources/uploads/frame.twig', $args);
        } else {
            $response = $this->download($request, $response, $args);
        }

        return $response;
    }

    public function open(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $file = Upload::find($args['upload_id']);

        if (empty($file)) {
            return $response;
        }

        $path = DOCROOT.'/'.$file->filepath;

        $fh = fopen($path, 'rb');
        $stream = new \Slim\Http\Stream($fh);

        $response = $response
            ->withHeader('Content-Type', mime_content_type($path))
            ->withBody($stream);

        return $response;
    }

    public function upload(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $response = $this->twig->render($response, '@resources/uploads/editor.twig', $args);

        return $response;
    }

    public function remove(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $response = $this->twig->render($response, '@resources/uploads/actions.twig', $args);

        return $response;
    }

    public function download(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $file = Upload::find($args['upload_id']);

        if (empty($file)) {
            return $response;
        }

        download($file->filepath);

        return $response;
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $response = $this->twig->render($response, '@resources/uploads/actions.twig', $args);

        return $response;
    }
}
