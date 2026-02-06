<?php
declare(strict_types=1);

use App\Controllers\AcademicYearsController;
use App\Controllers\TermsController;
use App\Controllers\SessionsController;
use App\Controllers\ClassesController;

/** @var \App\Core\Http\Router $router */

$router->get('/academic-years', [AcademicYearsController::class, 'index'])->middleware('auth', 'perm:classes.manage');
$router->get('/academic-years/create', [AcademicYearsController::class, 'create'])->middleware('auth', 'perm:classes.manage');
$router->post('/academic-years', [AcademicYearsController::class, 'store'])->middleware('auth', 'csrf', 'perm:classes.manage');
$router->get('/academic-years/{id}/edit', [AcademicYearsController::class, 'edit'])->middleware('auth', 'perm:classes.manage');
$router->post('/academic-years/{id}', [AcademicYearsController::class, 'update'])->middleware('auth', 'csrf', 'perm:classes.manage');
$router->post('/academic-years/bulk', [AcademicYearsController::class, 'bulk'])->middleware('auth', 'csrf', 'perm:classes.manage');

$router->get('/terms', [TermsController::class, 'index'])->middleware('auth', 'perm:classes.manage');
$router->get('/terms/create', [TermsController::class, 'create'])->middleware('auth', 'perm:classes.manage');
$router->post('/terms', [TermsController::class, 'store'])->middleware('auth', 'csrf', 'perm:classes.manage');
$router->get('/terms/{id}/edit', [TermsController::class, 'edit'])->middleware('auth', 'perm:classes.manage');
$router->post('/terms/{id}', [TermsController::class, 'update'])->middleware('auth', 'csrf', 'perm:classes.manage');

$router->get('/sessions', [SessionsController::class, 'index'])->middleware('auth', 'perm:classes.manage');
$router->get('/sessions/create', [SessionsController::class, 'create'])->middleware('auth', 'perm:classes.manage');
$router->post('/sessions', [SessionsController::class, 'store'])->middleware('auth', 'csrf', 'perm:classes.manage');
$router->get('/sessions/{id}/edit', [SessionsController::class, 'edit'])->middleware('auth', 'perm:classes.manage');
$router->post('/sessions/{id}', [SessionsController::class, 'update'])->middleware('auth', 'csrf', 'perm:classes.manage');

$router->get('/classes', [ClassesController::class, 'index'])->middleware('auth', 'perm:classes.view');
$router->get('/classes/create', [ClassesController::class, 'create'])->middleware('auth', 'perm:classes.manage');
$router->post('/classes', [ClassesController::class, 'store'])->middleware('auth', 'csrf', 'perm:classes.manage');
$router->get('/classes/{id}/edit', [ClassesController::class, 'edit'])->middleware('auth', 'perm:classes.manage');
$router->post('/classes/{id}', [ClassesController::class, 'update'])->middleware('auth', 'csrf', 'perm:classes.manage');
$router->post('/classes/bulk', [ClassesController::class, 'bulk'])->middleware('auth', 'csrf', 'perm:classes.manage');
