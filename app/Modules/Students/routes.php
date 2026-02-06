<?php
declare(strict_types=1);

use App\Controllers\StudentsController;

/** @var \App\Core\Http\Router $router */

$router->get('/students', [StudentsController::class, 'index'])->middleware('auth', 'perm:students.view');
$router->get('/students/create', [StudentsController::class, 'create'])->middleware('auth', 'perm:students.create');
$router->post('/students', [StudentsController::class, 'store'])->middleware('auth', 'csrf', 'perm:students.create');
$router->get('/students/{id}', [StudentsController::class, 'show'])->middleware('auth', 'perm:students.view');
$router->get('/students/{id}/edit', [StudentsController::class, 'edit'])->middleware('auth', 'perm:students.edit');
$router->post('/students/{id}', [StudentsController::class, 'update'])->middleware('auth', 'csrf', 'perm:students.edit');
$router->post('/students/bulk', [StudentsController::class, 'bulk'])->middleware('auth', 'csrf', 'perm:students.edit');
