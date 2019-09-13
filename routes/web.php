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

// Pagina principale
$app->get('/', 'Controllers\BaseController:index')
    ->setName('login');
$app->post('/', 'Controllers\BaseController:loginAction')
    ->add(GuestMiddleware::class);
$app->get('/logout/', 'Controllers\BaseController:logout')
    ->setName('logout')
    ->add(UserMiddleware::class);

// Configurazione iniziale
$app->group('', function () use ($app) {
    $app->get('/requirements/', 'Controllers\Config\RequirementsController:requirements')
        ->setName('requirements');

    $app->get('/configuration/', 'Controllers\Config\ConfigurationController:configuration')
        ->setName('configuration');
    $app->post('/configuration/', 'Controllers\Config\ConfigurationController:configurationSave')
        ->setName('configuration-save');

    $app->post('/configuration/test/', 'Controllers\Config\ConfigurationController:configurationTest')
        ->setName('configuration-test');

    $app->get('/init/', 'Controllers\Config\InitController:init')
        ->setName('init');
    $app->post('/init/', 'Controllers\Config\InitController:initSave')
        ->setName('init-save');

    $app->get('/update/', 'Controllers\Config\UpdateController:update')
        ->setName('update');
    $app->get('/update/progress/', 'Controllers\Config\UpdateController:updateProgress')
        ->setName('update-progress');
})->add(GuestMiddleware::class);

$app->group('', function () use ($app) {
    // Informazioni su OpenSTAManager
    $app->get('/info/', 'Controllers\InfoController:info')
        ->setName('info');

    // Segnalazione bug
    $app->get('/bug/', 'Controllers\InfoController:bug')
        ->setName('bug');
    $app->post('/bug/', 'Controllers\InfoController:bugSend');

    // Log di accesso
    $app->get('/logs/', 'Controllers\InfoController:logs')
        ->setName('logs');

    // Informazioni sull'utente
    $app->get('/user/', 'Controllers\InfoController:user')
        ->setName('user');

    $app->get('/password/', 'Controllers\InfoController:password')
        ->setName('user-password');
    $app->post('/password/', 'Controllers\InfoController:passwordPost');
})->add(UserMiddleware::class);

// Operazioni Ajax
$app->group('/ajax', function () use ($app) {
    $app->get('/select/', 'Controllers\AjaxController:select')
        ->setName('ajax-select');
    $app->get('/complete/', 'Controllers\AjaxController:complete')
        ->setName('ajax-complete');
    $app->get('/search/', 'Controllers\AjaxController:search')
        ->setName('ajax-search');

    // Messaggi flash
    $app->get('/flash/', 'Controllers\AjaxController:flash')
        ->setName('ajax-flash');

    // Sessioni
    $app->get('/session/', 'Controllers\AjaxController:sessionSet')
        ->setName('ajax-session');
    $app->get('/session-array/', 'Controllers\AjaxController:sessionSetArray')
        ->setName('ajax-session-array');

    // Dataload
    $app->get('/dataload/{module_id:[0-9]+}/[reference/{reference_id:[0-9]+}/]', 'Controllers\AjaxController:dataLoad')
        ->setName('ajax-dataload')
        ->add(PermissionMiddleware::class)
        ->add(ModuleMiddleware::class);
})->add(UserMiddleware::class);

// Hooks
$app->group('/hook', function () use ($app) {
    $app->get('/list/', 'Controllers\HookController:hooks')
        ->setName('hooks');

    $app->get('/lock/{hook_id:[0-9]+}/', 'Controllers\HookController:lock')
        ->setName('hook-lock');

    $app->get('/execute/{hook_id:[0-9]+}/{token/', 'Controllers\HookController:execute')
        ->setName('hook-execute');

    $app->get('/response/{hook_id:[0-9]+}/', 'Controllers\HookController:response')
        ->setName('hook-response');
})->add(UserMiddleware::class);

// Stampe
$app->group('/print', function () use ($app) {
    $app->get('/{print_id:[0-9]+}/[{record_id:[0-9]+}/]', 'Controllers\PrintController:view')
        ->setName('print');

    $app->get('/open/{print_id:[0-9]+}/[{record_id:[0-9]+}/]', 'Controllers\PrintController:open')
        ->setName('print-open');
})->add(UserMiddleware::class);

// Moduli
$app->group('/upload', function () use ($app) {
    $app->get('/{upload_id:[0-9]+}/', 'Controllers\UploadController:view')
        ->setName('upload-view');

    $app->get('/open/{upload_id:[0-9]+}/', 'Controllers\UploadController:open')
        ->setName('upload-open');

    $app->get('/download/{upload_id:[0-9]+}/', 'Controllers\UploadController:download')
        ->setName('upload-download');

    $app->get('/add/{module_id:[0-9]+}/{record_id:[0-9]+}/', 'Controllers\UploadController:index')
        ->setName('upload');

    $app->get('/remove/{upload_id:[0-9]+}/', 'Controllers\UploadController:remove')
        ->setName('upload-remove');
})->add(UserMiddleware::class);

// E-mail
$app->get('/mail/{mail_id:[0-9]+}/', 'MailController:index')
    ->setName('mail')
    ->add(UserMiddleware::class);

/*
Route::get('/', 'BaseController:index');

// Authentication routes
//Auth::routes();
Route::get('login', 'Auth\LoginController:showLoginForm')
        ->setName('login');
$app->post('login', 'Auth\LoginController:login');
$app->post('logout', 'Auth\LoginController:logout')
        ->setName('logout');

// Base routes
Route::get('/info', 'InfoController:index')
        ->setName('info');
Route::get('/bug', 'InfoController:bug')
        ->setName('bug');
Route::get('/user', 'InfoController:user')
        ->setName('user');
Route::get('/user-logs', 'InfoController:logs')
        ->setName('logs');

// Modules
Route::prefix('module/{module}')->group(function () {
   $app->get('', 'ModuleController:index')
        ->setName('module');

   $app->get('/{record_id:[0-9]+}', 'ModuleController:record')
        ->setName('module-record');
    $app->post('/{record_id:[0-9]+}', 'ModuleController:saveRecord');

   $app->get('/add', 'ModuleController:add')
        ->setName('module-add');
    $app->post('/add', 'ModuleController:addRecord');
});

// Plugins
Route::prefix('plugin')->group(function () {
   $app->get('/{plugin}', 'PluginController:index')
        ->setName('plugin');

   $app->get('/{plugin}/{record_id:[0-9]+}', 'PluginController:record')
        ->setName('plugin-record');
    $app->post('/{plugin}/{record_id:[0-9]+}', 'PluginController:saveRecord');

   $app->get('/add/{parent_id}/{plugin}', 'PluginController:record')
        ->setName('plugin-add');
    $app->post('/add/{parent_id}/{plugin}', 'PluginController:addRecord');
});

// Prints
Route::get('/print/{print_id:[0-9]+}', 'PrintController:index')
        ->setName('print');

// Mails
Route::get('/mail/{mail_id:[0-9]+}', 'MailController:index')
        ->setName('mail');
*/
