<?php
declare(strict_types=1);

use App\Controllers\LessonsController;

/** @var \App\Core\Http\Router $router */

$router->get('/lessons', [LessonsController::class, 'index'])->middleware('auth');
$router->get('/lessons/create', [LessonsController::class, 'create'])->middleware('auth');
$router->post('/lessons', [LessonsController::class, 'store'])->middleware('auth', 'csrf');
$router->get('/lessons/{id}', [LessonsController::class, 'show'])->middleware('auth');
$router->get('/lessons/{id}/copy', [LessonsController::class, 'copy'])->middleware('auth');
$router->get('/lessons/{id}/print', [LessonsController::class, 'print'])->middleware('auth');
$router->get('/lessons/{id}/edit', [LessonsController::class, 'edit'])->middleware('auth');
$router->post('/lessons/{id}', [LessonsController::class, 'update'])->middleware('auth', 'csrf');
