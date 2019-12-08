<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

use Middlewares\Authorization\GuestMiddleware;
use Middlewares\Authorization\PermissionMiddleware;
use Middlewares\Authorization\UserMiddleware;
use Middlewares\ModuleMiddleware;
use Slim\Routing\RouteCollectorProxy;

// Pagina principale
$app->get('/', 'Controllers\BaseController:index')
    ->setName('login');
$app->post('/', 'Controllers\BaseController:loginAction')
    ->add(GuestMiddleware::class);
$app->get('/logout/', 'Controllers\BaseController:logout')
    ->setName('logout')
    ->add(UserMiddleware::class);

// Configurazione iniziale
$app->group('', function (RouteCollectorProxy $group) {
    $group->get('/requirements/', 'Controllers\Config\RequirementsController:requirements')
        ->setName('requirements');

    $group->get('/configuration/', 'Controllers\Config\ConfigurationController:configuration')
        ->setName('configuration');
    $group->post('/configuration/', 'Controllers\Config\ConfigurationController:configurationSave')
        ->setName('configuration-save');

    $group->post('/configuration/test/', 'Controllers\Config\ConfigurationController:configurationTest')
        ->setName('configuration-test');

    $group->get('/init/', 'Controllers\Config\InitController:init')
        ->setName('init');
    $group->post('/init/', 'Controllers\Config\InitController:initSave')
        ->setName('init-save');

    $group->get('/update/', 'Controllers\Config\UpdateController:update')
        ->setName('update');
    $group->get('/update/progress/', 'Controllers\Config\UpdateController:updateProgress')
        ->setName('update-progress');
})->add(GuestMiddleware::class);

$app->group('', function (RouteCollectorProxy $group) {
    // Informazioni su OpenSTAManager
    $group->get('/info/', 'Controllers\InfoController:info')
        ->setName('info');

    // Segnalazione bug
    $group->get('/bug/', 'Controllers\InfoController:bug')
        ->setName('bug');
    $group->post('/bug/', 'Controllers\InfoController:bugSend');

    // Log di accesso
    $group->get('/logs/', 'Controllers\InfoController:logs')
        ->setName('logs');

    // Informazioni sull'utente
    $group->get('/user/', 'Controllers\InfoController:user')
        ->setName('user');

    $group->get('/password/', 'Controllers\InfoController:password')
        ->setName('user-password');
    $group->post('/password/', 'Controllers\InfoController:passwordPost');
})->add(UserMiddleware::class);

// Operazioni Ajax
$app->group('/ajax', function (RouteCollectorProxy $group) {
    $group->get('/select/', 'Controllers\AjaxController:select')
        ->setName('ajax-select');
    $group->get('/complete/', 'Controllers\AjaxController:complete')
        ->setName('ajax-complete');
    $group->get('/search/', 'Controllers\AjaxController:search')
        ->setName('ajax-search');

    // Messaggi flash
    $group->get('/flash/', 'Controllers\AjaxController:flash')
        ->setName('ajax-flash');

    // Sessioni
    $group->get('/session/', 'Controllers\AjaxController:sessionSet')
        ->setName('ajax-session');
    $group->get('/session-array/', 'Controllers\AjaxController:sessionSetArray')
        ->setName('ajax-session-array');

    // Dataload
    $group->get('/dataload/{module_id:[0-9]+}/[reference/{reference_id:[0-9]+}/]', 'Controllers\AjaxController:dataLoad')
        ->setName('ajax-dataload')
        ->add(PermissionMiddleware::class)
        ->add(ModuleMiddleware::class);
})->add(UserMiddleware::class);

// Hooks
$app->group('/hook', function (RouteCollectorProxy $group) {
    $group->get('/list/', 'Controllers\HookController:hooks')
        ->setName('hooks');

    $group->get('/lock/{hook_id:[0-9]+}/', 'Controllers\HookController:lock')
        ->setName('hook-lock');

    $group->get('/execute/{hook_id:[0-9]+}/{token/', 'Controllers\HookController:execute')
        ->setName('hook-execute');

    $group->get('/response/{hook_id:[0-9]+}/', 'Controllers\HookController:response')
        ->setName('hook-response');
})->add(UserMiddleware::class);

// Stampe
$app->group('/print', function (RouteCollectorProxy $group) {
    $group->get('/{print_id:[0-9]+}/[{record_id:[0-9]+}/]', 'Controllers\PrintController:view')
        ->setName('print');

    $group->get('/open/{print_id:[0-9]+}/[{record_id:[0-9]+}/]', 'Controllers\PrintController:open')
        ->setName('print-open');
})->add(UserMiddleware::class);

// Moduli
$app->group('/upload', function (RouteCollectorProxy $group) {
    $group->get('/{upload_id:[0-9]+}/', 'Controllers\UploadController:view')
        ->setName('upload-view');

    $group->get('/open/{upload_id:[0-9]+}/', 'Controllers\UploadController:open')
        ->setName('upload-open');

    $group->get('/download/{upload_id:[0-9]+}/', 'Controllers\UploadController:download')
        ->setName('upload-download');

    $group->get('/add/{module_id:[0-9]+}/{record_id:[0-9]+}/', 'Controllers\UploadController:index')
        ->setName('upload');

    $group->get('/remove/{upload_id:[0-9]+}/', 'Controllers\UploadController:remove')
        ->setName('upload-remove');
})->add(UserMiddleware::class);

// E-mail
$app->get('/mail/{mail_id:[0-9]+}/', 'MailController:index')
    ->setName('mail')
    ->add(UserMiddleware::class);
