<?php
declare(strict_types=1);

use App\Controllers\ImportsController;

/** @var \App\Core\Http\Router $router */

$router->get('/imports', [ImportsController::class, 'index'])->middleware('auth');
$router->get('/imports/create', [ImportsController::class, 'create'])->middleware('auth');
$router->post('/imports/preview', [ImportsController::class, 'preview'])->middleware('auth', 'csrf');
$router->post('/imports', [ImportsController::class, 'store'])->middleware('auth', 'csrf');
$router->get('/imports/{id}', [ImportsController::class, 'show'])->middleware('auth');
$router->get('/imports/template/{type}', [ImportsController::class, 'template'])->middleware('auth');
