<?php

declare(strict_types=1);

use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Config\Repository;
use WTD\Container\Container;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$basePath = dirname(__DIR__);
$container = new Container();
$config = new Repository([
    'app.name' => 'WTD Core',
    'app.env' => $_ENV['APP_ENV'] ?? 'production',
    'app.debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
]);

$app = new Application($basePath, $container, $config);
$app->register(CoreServiceProvider::class);
$app->boot();

return $app;
