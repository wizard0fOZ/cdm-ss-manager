<?php
declare(strict_types=1);

use App\Controllers\PublicController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PasswordController;

/** @var \App\Core\Http\Router $router */

// Public main page
$router->get('/', [PublicController::class, 'home']);
$router->get('/public/announcements', [PublicController::class, 'announcements']);

// health + auth
$router->get('/health', [DashboardController::class, 'health']);
$router->get('/login',  [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'doLogin'])->middleware('csrf');
$router->post('/logout',[AuthController::class, 'logout'])->middleware('csrf', 'auth');
$router->get('/password/change', [PasswordController::class, 'show'])->middleware('auth');
$router->post('/password/change', [PasswordController::class, 'update'])->middleware('auth', 'csrf');

// protected
$router->get('/dashboard', [DashboardController::class, 'index'])->middleware('auth');

// modules
require dirname(__DIR__) . '/app/Modules/Students/routes.php';
require dirname(__DIR__) . '/app/Modules/Academic/routes.php';
require dirname(__DIR__) . '/app/Modules/Attendance/routes.php';
require dirname(__DIR__) . '/app/Modules/Lessons/routes.php';
require dirname(__DIR__) . '/app/Modules/FaithBook/routes.php';
require dirname(__DIR__) . '/app/Modules/Training/routes.php';
require dirname(__DIR__) . '/app/Modules/Bulletins/routes.php';
require dirname(__DIR__) . '/app/Modules/Calendar/routes.php';
