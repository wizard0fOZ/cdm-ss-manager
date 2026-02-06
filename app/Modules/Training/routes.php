<?php
declare(strict_types=1);

use App\Controllers\TrainingController;

/** @var \App\Core\Http\Router $router */

$router->get('/training', [TrainingController::class, 'index'])->middleware('auth');
$router->get('/training/create', [TrainingController::class, 'create'])->middleware('auth');
$router->post('/training', [TrainingController::class, 'store'])->middleware('auth', 'csrf');
$router->get('/training/{id}/edit', [TrainingController::class, 'edit'])->middleware('auth');
$router->post('/training/{id}', [TrainingController::class, 'update'])->middleware('auth', 'csrf');
