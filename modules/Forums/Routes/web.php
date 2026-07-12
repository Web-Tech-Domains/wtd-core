<?php

declare(strict_types=1);

use Modules\Forums\Http\Controllers\ForumsController;
use WTD\Routing\Router;

/** @var Router $router */
$router->get('/forums', [ForumsController::class, 'index'])->name('forums.index');
$router->post('/forums/topics', [ForumsController::class, 'createTopic'])->name('forums.topics.store');
$router->post('/forums/login', [ForumsController::class, 'login'])->name('forums.login');
$router->post('/forums/logout', [ForumsController::class, 'logout'])->name('forums.logout');

