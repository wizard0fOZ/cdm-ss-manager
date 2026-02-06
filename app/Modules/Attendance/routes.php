<?php
declare(strict_types=1);

use App\Controllers\AttendanceController;

/** @var \App\Core\Http\Router $router */

$router->get('/attendance', [AttendanceController::class, 'index'])->middleware('auth', 'perm:attendance.view');
$router->get('/attendance/{id}', [AttendanceController::class, 'take'])->middleware('auth', 'perm:attendance.view');
$router->post('/attendance/{id}', [AttendanceController::class, 'save'])->middleware('auth', 'csrf', 'perm:attendance.mark');
$router->post('/attendance/{id}/lock', [AttendanceController::class, 'lock'])->middleware('auth', 'csrf', 'perm:attendance.lock');
$router->post('/attendance/{id}/unlock', [AttendanceController::class, 'unlock'])->middleware('auth', 'csrf', 'perm:attendance.lock');
