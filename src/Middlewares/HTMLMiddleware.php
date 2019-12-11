<?php

namespace Middlewares;

use HTMLBuilder\HTMLBuilder;
use Intl\Formatter;
use Modules;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Stream;
use Translator;

/**
 * Middlware per la gestione della lingua del progetto.
 *
 * @since 2.5
 */
class HTMLMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $html = $response->getBody();

        $id_module = Modules::getCurrent()['id'];
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

    protected function getTranslator($locale)
    {
        $translator = new Translator();

        $translator->addLocalePath(DOCROOT.'/resources/locale');
        $translator->addLocalePath(DOCROOT.'/modules/*/locale');

        $translator->setLocale($locale);

        return $translator;
    }

    protected function getFormatter($locale, $options)
    {
        $formatter = new Formatter(
            $locale,
            empty($options['timestamp']) ? 'd/m/Y H:i' : $options['timestamp'],
            empty($options['date']) ? 'd/m/Y' : $options['date'],
            empty($options['time']) ? 'H:i' : $options['time']
        );

        $formatter->setPrecision(auth()->check() ? setting('Cifre decimali per importi') : 2);

        return $formatter;
    }

    protected function addFilters($twig)
    {
        $list = [
            'timestamp' => 'timestampFormat',
            'date' => 'dateFormat',
            'time' => 'timeFormat',
            'money' => 'moneyFormat',
        ];

        foreach ($list as $name => $function) {
            $filter = new \Twig\TwigFilter($name, $function);
            $twig->getEnvironment()->addFilter($filter);
        }
    }

    protected function addFunctions($twig)
    {
        $list = [
            'currency' => 'currency',
        ];

        foreach ($list as $name => $function) {
            $function = new \Twig\TwigFunction($name, $function);
            $twig->getEnvironment()->addFunction($function);
        }
    }
}
