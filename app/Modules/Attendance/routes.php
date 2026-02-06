<?php
declare(strict_types=1);

use App\Controllers\AttendanceController;

/** @var \App\Core\Http\Router $router */

$router->get('/attendance', [AttendanceController::class, 'index'])->middleware('auth');
$router->get('/attendance/{id}', [AttendanceController::class, 'take'])->middleware('auth');
$router->post('/attendance/{id}', [AttendanceController::class, 'save'])->middleware('auth', 'csrf');
$router->post('/attendance/{id}/lock', [AttendanceController::class, 'lock'])->middleware('auth', 'csrf');
$router->post('/attendance/{id}/unlock', [AttendanceController::class, 'unlock'])->middleware('auth', 'csrf');
