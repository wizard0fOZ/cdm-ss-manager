<?php
declare(strict_types=1);

use App\Controllers\CalendarController;

/** @var \App\Core\Http\Router $router */

$router->get('/calendar', [CalendarController::class, 'index'])->middleware('auth');
$router->get('/calendar/day', [CalendarController::class, 'day'])->middleware('auth');
$router->get('/calendar/export', [CalendarController::class, 'export'])->middleware('auth');
$router->get('/calendar/ical', [CalendarController::class, 'exportIcal'])->middleware('auth');
$router->get('/calendar/create', [CalendarController::class, 'create'])->middleware('auth', 'perm:calendar.manage');
$router->post('/calendar', [CalendarController::class, 'store'])->middleware('auth', 'csrf', 'perm:calendar.manage');
$router->get('/calendar/{id}/edit', [CalendarController::class, 'edit'])->middleware('auth', 'perm:calendar.manage');
$router->post('/calendar/{id}', [CalendarController::class, 'update'])->middleware('auth', 'csrf', 'perm:calendar.manage');
