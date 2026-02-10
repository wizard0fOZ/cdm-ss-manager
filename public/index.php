<?php
declare(strict_types=1);

// Production error settings — never display errors to users
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Support\Env;
use App\Core\Support\Logger;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\Router;

// Load env
Env::load(dirname(__DIR__));

try {
    // Create request/response/router
    $request = Request::fromGlobals();
    $response = new Response();
    $router = new Router($request, $response);

    // Register routes
    require dirname(__DIR__) . '/routes/web.php';

    // Dispatch
    $router->dispatch();
} catch (\Throwable $e) {
    Logger::error($e->getMessage(), [
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    $isProduction = (Env::get('APP_ENV', 'production') === 'production');

    http_response_code(500);

    if (!$isProduction) {
        echo '<h1>500 — Server Error</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        require __DIR__ . '/../app/Views/errors/500.php';
    }
}
