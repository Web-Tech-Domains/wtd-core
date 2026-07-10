<?php

declare(strict_types=1);

use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Config\Loader;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Support\Env;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$basePath = dirname(__DIR__);
Env::load($basePath . DIRECTORY_SEPARATOR . '.env');

$container = new Container();
$config = new Repository();
(new Loader($config))->loadDirectory($basePath . DIRECTORY_SEPARATOR . 'config');

$app = new Application($basePath, $container, $config);
$app->register(CoreServiceProvider::class);
$app->boot();

return $app;
