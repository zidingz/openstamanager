<?php

namespace Controllers;

use Auth;
use Controllers\Config\ConfigurationController;
use Controllers\Config\InitController;
use Controllers\Config\RequirementsController;
use Modules\Module;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Update;

class RetroController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
dd($request);
    }
}
