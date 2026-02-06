<?php
declare(strict_types=1);

use App\Controllers\LessonsController;

/** @var \App\Core\Http\Router $router */

$router->get('/lessons', [LessonsController::class, 'index'])->middleware('auth', 'perm:lessons.view');
$router->get('/lessons/create', [LessonsController::class, 'create'])->middleware('auth', 'perm:lessons.create');
$router->post('/lessons', [LessonsController::class, 'store'])->middleware('auth', 'csrf', 'perm:lessons.create');
$router->get('/lessons/{id}', [LessonsController::class, 'show'])->middleware('auth', 'perm:lessons.view');
$router->get('/lessons/{id}/copy', [LessonsController::class, 'copy'])->middleware('auth', 'perm:lessons.edit');
$router->get('/lessons/{id}/print', [LessonsController::class, 'print'])->middleware('auth', 'perm:lessons.view');
$router->get('/lessons/{id}/edit', [LessonsController::class, 'edit'])->middleware('auth', 'perm:lessons.edit');
$router->post('/lessons/{id}', [LessonsController::class, 'update'])->middleware('auth', 'csrf', 'perm:lessons.edit');
