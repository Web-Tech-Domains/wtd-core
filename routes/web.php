<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use WTD\Routing\Router;

/** @var Router $router */
$router->get('/', [HomeController::class, 'index'])->name('home');

$router->get('/health', [HomeController::class, 'health'])->name('health');
$router->get('/stream', [HomeController::class, 'stream'])->name('stream');
$router->get('/download', [HomeController::class, 'download'])->name('download');

$router->group('/api', static function (Router $router): void {
    $router->get('/status', [HomeController::class, 'apiStatus'])->name('api.status');
});
