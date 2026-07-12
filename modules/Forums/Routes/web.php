<?php

declare(strict_types=1);

use Modules\Forums\Http\Controllers\ForumsController;
use WTD\Routing\Router;

/** @var Router $router */
$router->get('/forums', [ForumsController::class, 'index'])->name('forums.index');

