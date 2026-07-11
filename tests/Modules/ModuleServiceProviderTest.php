<?php

declare(strict_types=1);

namespace Tests\Modules;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\Http\HttpServiceProvider;
use WTD\Http\Request;
use WTD\Modules\ModuleServiceProvider;
use WTD\Routing\Router;

final class ModuleServiceProviderTest extends TestCase
{
    public function testModuleServiceProviderLoadsModuleRoutes(): void
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $routePath = 'tests/tmp/modules/routes.php';
        $absoluteRoutePath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $routePath);

        if (!is_dir(dirname($absoluteRoutePath))) {
            mkdir(dirname($absoluteRoutePath), 0775, true);
        }

        file_put_contents($absoluteRoutePath, <<<'PHP'
<?php

declare(strict_types=1);

use WTD\Http\Response;
use WTD\Routing\Router;

/** @var Router $router */
$router->get('/module-health', static fn (): Response => Response::make('module-ok'));
PHP);

        $container = new Container();
        $container->singleton(Filesystem::class);

        $app = new Application(
            $basePath,
            $container,
            new Repository([
                'modules.enabled' => [[
                    'name' => 'TestModule',
                    'routes' => $routePath,
                ]],
            ]),
        );
        $app->register(HttpServiceProvider::class);
        $app->register(ModuleServiceProvider::class);
        $app->boot();

        /** @var Router $router */
        $router = $app->container()->get(Router::class);
        $response = $router->dispatch(new Request('GET', '/module-health'));

        self::assertSame('module-ok', $response->content());
    }

    public function testModuleServiceProviderAutoDiscoversModuleManifests(): void
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $moduleRoot = $basePath . '/modules/AutoTest';
        $routePath = $moduleRoot . '/Routes/web.php';

        if (!is_dir(dirname($routePath))) {
            mkdir(dirname($routePath), 0775, true);
        }

        file_put_contents($moduleRoot . '/module.php', <<<'PHP'
<?php

declare(strict_types=1);

return [
    'name' => 'AutoTest',
    'routes' => 'modules/AutoTest/Routes/web.php',
];
PHP);
        file_put_contents($routePath, <<<'PHP'
<?php

declare(strict_types=1);

use WTD\Http\Response;
use WTD\Routing\Router;

/** @var Router $router */
$router->get('/auto-test', static fn (): Response => Response::make('auto-ok'));
PHP);

        try {
            $container = new Container();
            $container->singleton(Filesystem::class);

            $app = new Application(
                $basePath,
                $container,
                new Repository([
                    'modules.enabled' => [],
                    'modules.auto_discover' => true,
                ]),
            );
            $app->register(HttpServiceProvider::class);
            $app->register(ModuleServiceProvider::class);
            $app->boot();

            /** @var Router $router */
            $router = $app->container()->get(Router::class);
            $response = $router->dispatch(new Request('GET', '/auto-test'));

            self::assertSame('auto-ok', $response->content());
        } finally {
            @unlink($routePath);
            @unlink($moduleRoot . '/module.php');
            @rmdir($moduleRoot . '/Routes');
            @rmdir($moduleRoot);
        }
    }
}
