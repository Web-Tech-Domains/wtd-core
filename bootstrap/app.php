<?php

declare(strict_types=1);

use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Application\ProviderBootstrapper;
use WTD\Config\Cache;
use WTD\Config\Loader;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Console\ConsoleServiceProvider;
use WTD\Filesystem\Filesystem;
use WTD\Http\HttpServiceProvider;
use WTD\Support\Env;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$basePath = dirname(__DIR__);
Env::load($basePath . DIRECTORY_SEPARATOR . '.env');

$container = new Container();
$config = new Repository();
$filesystem = new Filesystem();
$configCache = new Cache($filesystem, $basePath . DIRECTORY_SEPARATOR . 'storage/framework/config.php');

if ($configCache->exists()) {
    $config->replace($configCache->load());
} else {
    (new Loader($config))->loadDirectory($basePath . DIRECTORY_SEPARATOR . 'config');
}

$app = new Application($basePath, $container, $config);
$app->register(CoreServiceProvider::class);
$app->register(ConsoleServiceProvider::class);
$app->register(HttpServiceProvider::class);

$providers = $config->get('app.providers', []);
if (is_array($providers)) {
    /** @var list<class-string<WTD\Support\ServiceProvider>> $providers */
    (new ProviderBootstrapper($app))->bootstrap($providers);
}

$app->boot();

return $app;
