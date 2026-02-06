<?php
declare(strict_types=1);

use App\Controllers\AnnouncementsController;

/** @var \App\Core\Http\Router $router */

$router->get('/announcements', [AnnouncementsController::class, 'index'])->middleware('auth');
$router->get('/announcements/create', [AnnouncementsController::class, 'create'])->middleware('auth');
$router->post('/announcements', [AnnouncementsController::class, 'store'])->middleware('auth', 'csrf');
$router->get('/announcements/{id}/edit', [AnnouncementsController::class, 'edit'])->middleware('auth');
$router->post('/announcements/{id}', [AnnouncementsController::class, 'update'])->middleware('auth', 'csrf');
