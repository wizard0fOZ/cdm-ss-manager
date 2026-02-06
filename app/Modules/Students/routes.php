<?php
declare(strict_types=1);

use App\Controllers\StudentsController;

/** @var \App\Core\Http\Router $router */

$router->get('/students', [StudentsController::class, 'index'])->middleware('auth');
$router->get('/students/create', [StudentsController::class, 'create'])->middleware('auth');
$router->post('/students', [StudentsController::class, 'store'])->middleware('auth', 'csrf');
$router->get('/students/{id}', [StudentsController::class, 'show'])->middleware('auth');
$router->get('/students/{id}/edit', [StudentsController::class, 'edit'])->middleware('auth');
$router->post('/students/{id}', [StudentsController::class, 'update'])->middleware('auth', 'csrf');
$router->post('/students/bulk', [StudentsController::class, 'bulk'])->middleware('auth', 'csrf');
