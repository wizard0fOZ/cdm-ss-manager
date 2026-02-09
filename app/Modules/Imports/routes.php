<?php
declare(strict_types=1);

use App\Controllers\ImportsController;

/** @var \App\Core\Http\Router $router */

$router->get('/imports', [ImportsController::class, 'index'])->middleware('auth', 'perm:imports.view');
$router->get('/imports/create', [ImportsController::class, 'create'])->middleware('auth', 'perm:imports.run');
$router->post('/imports/preview', [ImportsController::class, 'preview'])->middleware('auth', 'csrf', 'perm:imports.run');
$router->post('/imports/preview/partial', [ImportsController::class, 'previewPartial'])->middleware('auth', 'csrf', 'perm:imports.run');
$router->post('/imports', [ImportsController::class, 'store'])->middleware('auth', 'csrf', 'perm:imports.run');
$router->get('/imports/{id}', [ImportsController::class, 'show'])->middleware('auth', 'perm:imports.view');
$router->get('/imports/template/{type}', [ImportsController::class, 'template'])->middleware('auth', 'perm:imports.view');
