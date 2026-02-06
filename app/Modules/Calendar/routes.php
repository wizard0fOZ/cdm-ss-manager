<?php
declare(strict_types=1);

use App\Controllers\CalendarController;

/** @var \App\Core\Http\Router $router */

$router->get('/calendar', [CalendarController::class, 'index'])->middleware('auth');
$router->get('/calendar/export', [CalendarController::class, 'export'])->middleware('auth');
$router->get('/calendar/create', [CalendarController::class, 'create'])->middleware('auth');
$router->post('/calendar', [CalendarController::class, 'store'])->middleware('auth', 'csrf');
$router->get('/calendar/{id}/edit', [CalendarController::class, 'edit'])->middleware('auth');
$router->post('/calendar/{id}', [CalendarController::class, 'update'])->middleware('auth', 'csrf');
