<?php

declare(strict_types=1);

use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Application\ProviderBootstrapper;
use WTD\AI\AIServiceProvider;
use WTD\CLI\CliServiceProvider;
use WTD\Config\Cache;
use WTD\Config\Loader;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Console\ConsoleServiceProvider;
use WTD\Database\DatabaseServiceProvider;
use WTD\DeveloperExperience\DeveloperExperienceServiceProvider;
use WTD\Filesystem\Filesystem;
use WTD\Http\HttpServiceProvider;
use WTD\Marketplace\MarketplaceServiceProvider;
use WTD\Monitoring\MonitoringServiceProvider;
use WTD\Security\SecurityServiceProvider;
use WTD\Support\Env;
use WTD\Tenancy\TenancyServiceProvider;
use WTD\Validation\ValidationServiceProvider;
use WTD\View\ViewServiceProvider;
use WTD\WebSocket\WebSocketServiceProvider;

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
$app->register(CliServiceProvider::class);
$app->register(HttpServiceProvider::class);
$app->register(ValidationServiceProvider::class);
$app->register(DatabaseServiceProvider::class);
$app->register(SecurityServiceProvider::class);
$app->register(ViewServiceProvider::class);
$app->register(WebSocketServiceProvider::class);
$app->register(DeveloperExperienceServiceProvider::class);
$app->register(MarketplaceServiceProvider::class);
$app->register(TenancyServiceProvider::class);
$app->register(AIServiceProvider::class);
$app->register(MonitoringServiceProvider::class);

$providers = $config->get('app.providers', []);
if (is_array($providers)) {
    /** @var list<class-string<WTD\Support\ServiceProvider>> $providers */
    (new ProviderBootstrapper($app))->bootstrap($providers);
}

$app->boot();

return $app;
