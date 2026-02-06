<?php
declare(strict_types=1);

use App\Controllers\FaithBookController;

/** @var \App\Core\Http\Router $router */

$router->get('/faith-book', [FaithBookController::class, 'index'])->middleware('auth');
$router->get('/faith-book/{id}', [FaithBookController::class, 'show'])->middleware('auth');
$router->get('/faith-book/{id}/create', [FaithBookController::class, 'create'])->middleware('auth');
$router->post('/faith-book/{id}', [FaithBookController::class, 'store'])->middleware('auth', 'csrf');
$router->get('/faith-book/{id}/export', [FaithBookController::class, 'export'])->middleware('auth');
$router->get('/faith-book/{id}/print', [FaithBookController::class, 'print'])->middleware('auth');
$router->get('/faith-book/{id}/pdf', [FaithBookController::class, 'pdf'])->middleware('auth');
