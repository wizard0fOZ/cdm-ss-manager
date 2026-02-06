<?php
declare(strict_types=1);

use App\Controllers\FaithBookController;

/** @var \App\Core\Http\Router $router */

$router->get('/faith-book', [FaithBookController::class, 'index'])->middleware('auth', 'perm:faithbook.view');
$router->get('/faith-book/{id}', [FaithBookController::class, 'show'])->middleware('auth', 'perm:faithbook.view');
$router->get('/faith-book/{id}/create', [FaithBookController::class, 'create'])->middleware('auth', 'perm:faithbook.write');
$router->post('/faith-book/{id}', [FaithBookController::class, 'store'])->middleware('auth', 'csrf', 'perm:faithbook.write');
$router->get('/faith-book/{id}/export', [FaithBookController::class, 'export'])->middleware('auth', 'perm:faithbook.view');
$router->get('/faith-book/{id}/print', [FaithBookController::class, 'print'])->middleware('auth', 'perm:faithbook.view');
$router->get('/faith-book/{id}/pdf', [FaithBookController::class, 'pdf'])->middleware('auth', 'perm:faithbook.view');
