<?php
declare(strict_types=1);

use App\Controllers\PublicController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;

/** @var \App\Core\Http\Router $router */

// Public main page
$router->get('/', [PublicController::class, 'home']);

// health + auth
$router->get('/health', [DashboardController::class, 'health']);
$router->get('/login',  [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'doLogin'])->middleware('csrf');
$router->post('/logout',[AuthController::class, 'logout'])->middleware('csrf', 'auth');

// protected
$router->get('/dashboard', [DashboardController::class, 'index'])->middleware('auth');

// modules
require dirname(__DIR__) . '/app/Modules/Students/routes.php';
require dirname(__DIR__) . '/app/Modules/Academic/routes.php';
