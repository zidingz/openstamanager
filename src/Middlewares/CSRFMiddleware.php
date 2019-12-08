<?php

namespace Middlewares;

use Psr\Container\ContainerInterface;
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

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
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
