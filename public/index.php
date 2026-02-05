<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Support\Env;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\Router;

// Load env
Env::load(dirname(__DIR__));

// Create request/response/router
$request = Request::fromGlobals();
$response = new Response();
$router = new Router($request, $response);

// Register routes
require dirname(__DIR__) . '/routes/web.php';

// Dispatch
$router->dispatch();
