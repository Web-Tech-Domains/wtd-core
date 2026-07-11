<?php

declare(strict_types=1);

use Modules\Blog\Http\Controllers\BlogController;
use WTD\Routing\Router;

/** @var Router $router */
$router->get('/blog', [BlogController::class, 'index'])->name('blog.index');
