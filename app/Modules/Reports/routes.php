<?php
declare(strict_types=1);

use App\Controllers\ReportsController;

/** @var \App\Core\Http\Router $router */

$router->get('/reports', [ReportsController::class, 'index'])->middleware('auth');
$router->get('/reports/pdf', [ReportsController::class, 'pdf'])->middleware('auth');
$router->get('/reports/csv', [ReportsController::class, 'csv'])->middleware('auth');
