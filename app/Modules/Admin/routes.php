<?php
declare(strict_types=1);

use App\Controllers\AdminController;

/** @var \App\Core\Http\Router $router */

$router->get('/admin', [AdminController::class, 'index'])->middleware('auth', 'perm:admin.users');
$router->get('/admin/users', [AdminController::class, 'users'])->middleware('auth', 'perm:admin.users');
$router->get('/admin/users/create', [AdminController::class, 'create'])->middleware('auth', 'perm:admin.users');
$router->post('/admin/users', [AdminController::class, 'store'])->middleware('auth', 'csrf', 'perm:admin.users');
$router->get('/admin/users/{id}', [AdminController::class, 'edit'])->middleware('auth', 'perm:admin.users');
$router->post('/admin/users/{id}', [AdminController::class, 'update'])->middleware('auth', 'csrf', 'perm:admin.users');
$router->get('/admin/roles', [AdminController::class, 'roles'])->middleware('auth', 'perm:admin.roles');
$router->post('/admin/roles', [AdminController::class, 'updateRoles'])->middleware('auth', 'csrf', 'perm:admin.roles');
$router->get('/admin/settings', [AdminController::class, 'settings'])->middleware('auth', 'perm:admin.settings');
$router->post('/admin/settings', [AdminController::class, 'updateSettings'])->middleware('auth', 'csrf', 'perm:admin.settings');
$router->get('/admin/monitoring', [AdminController::class, 'monitoring'])->middleware('auth', 'perm:admin.audit');
$router->get('/admin/audits/{id}', [AdminController::class, 'auditShow'])->middleware('auth', 'perm:admin.audit');
$router->get('/admin/maintenance', [AdminController::class, 'maintenance'])->middleware('auth', 'perm:admin.settings');
$router->post('/admin/maintenance', [AdminController::class, 'maintenanceAction'])->middleware('auth', 'csrf', 'perm:admin.settings');
$router->post('/admin/role-switch', [AdminController::class, 'switchRole'])->middleware('auth', 'csrf');
