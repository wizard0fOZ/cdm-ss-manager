<?php
declare(strict_types=1);

use App\Controllers\AnnouncementsController;

/** @var \App\Core\Http\Router $router */

$router->get('/announcements', [AnnouncementsController::class, 'index'])->middleware('auth');
$router->get('/announcements/create', [AnnouncementsController::class, 'create'])->middleware('auth', 'perm:bulletins.manage');
$router->post('/announcements', [AnnouncementsController::class, 'store'])->middleware('auth', 'csrf', 'perm:bulletins.manage');
$router->get('/announcements/{id}/edit', [AnnouncementsController::class, 'edit'])->middleware('auth', 'perm:bulletins.manage');
$router->post('/announcements/{id}', [AnnouncementsController::class, 'update'])->middleware('auth', 'csrf', 'perm:bulletins.manage');
