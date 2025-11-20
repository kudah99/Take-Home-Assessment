<?php

/*
|--------------------------------------------------------------------------
| Laravel Front Controller
|--------------------------------------------------------------------------
|
| Laravel - A PHP Framework For Web Artisans
|
| When a user requests your application, this front controller is loaded
| and then all of your application's requests are handled by Laravel's
| service provider. The front controller is responsible for starting
| the request to the application.
|
*/

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll require it into the
| script here so that we do not have to worry about manually loading any
| of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Bootstrap Laravel And Handle The Request
|--------------------------------------------------------------------------
|
| Now we're ready to receive requests from the application. This script
| services requests to the application's front controller so that we can
| separate the concerns of routing from the logic of your application.
| The output can be either JSON, HTML or other formats based on what
| the application returns based on the request.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
