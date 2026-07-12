<?php

declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Cache\CacheManager;
use WTD\Cache\CacheRepository;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Cookie\Cookie;
use WTD\Filesystem\Filesystem;
use WTD\Hooks\HookManager;
use WTD\Http\Client\HttpClient;
use WTD\Session\SessionStore;
use WTD\View\AssetManager;
use WTD\View\ViewRenderer;

final class HelpersTest extends TestCase
{
    public function testPathHelpersReturnProjectPaths(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        self::assertSame(dirname(__DIR__, 2), base_path());
        self::assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'app', app_path());
        self::assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config', config_path());
        self::assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public', public_path());
        self::assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage', storage_path());
    }

    public function testEnvHelperCastsCommonValues(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $_ENV['WTD_TEST_TRUE'] = 'true';
        $_ENV['WTD_TEST_FALSE'] = 'false';
        $_ENV['WTD_TEST_EMPTY'] = 'empty';
        $_ENV['WTD_TEST_NULL'] = 'null';
        $_ENV['WTD_TEST_TEXT'] = 'wtd';

        try {
            self::assertTrue(env('WTD_TEST_TRUE'));
            self::assertFalse(env('WTD_TEST_FALSE'));
            self::assertSame('', env('WTD_TEST_EMPTY'));
            self::assertNull(env('WTD_TEST_NULL'));
            self::assertSame('wtd', env('WTD_TEST_TEXT'));
            self::assertSame('fallback', env('WTD_TEST_MISSING', 'fallback'));
        } finally {
            unset(
                $_ENV['WTD_TEST_TRUE'],
                $_ENV['WTD_TEST_FALSE'],
                $_ENV['WTD_TEST_EMPTY'],
                $_ENV['WTD_TEST_NULL'],
                $_ENV['WTD_TEST_TEXT'],
            );
        }
    }

    public function testViewHelpersRenderNormalAndModuleViews(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $files = new Filesystem();
        $files->put($basePath . '/tests/tmp/helper-views/home.php', 'Hello {{ name }}');
        $files->put($basePath . '/modules/HelperModule/Resources/views/pages/index.php', 'Module {{ name }}');
        $container = new Container();
        $app = new Application($basePath, $container, new Repository([
            'view.path' => 'tests/tmp/helper-views',
            'view.extension' => '.php',
        ]));
        $container->singleton(Filesystem::class, static fn (): Filesystem => $files);
        $container->singleton(ViewRenderer::class);
        $GLOBALS['wtd_app'] = $app;

        try {
            self::assertSame('Hello WTD', view('home', ['name' => 'WTD']));
            self::assertSame('Module Blog', module_view('HelperModule', 'pages.index', ['name' => 'Blog']));
        } finally {
            unset($GLOBALS['wtd_app']);
            @unlink($basePath . '/tests/tmp/helper-views/home.php');
            @rmdir($basePath . '/tests/tmp/helper-views');
            @unlink($basePath . '/modules/HelperModule/Resources/views/pages/index.php');
            @rmdir($basePath . '/modules/HelperModule/Resources/views/pages');
            @rmdir($basePath . '/modules/HelperModule/Resources/views');
            @rmdir($basePath . '/modules/HelperModule/Resources');
            @rmdir($basePath . '/modules/HelperModule');
        }
    }

    public function testViteHelperRendersAssetTags(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $files = new Filesystem();
        $files->put($basePath . '/tests/tmp/helper-assets/public/build/.vite/manifest.json', json_encode([
            'resources/js/app.js' => [
                'file' => 'assets/app.js',
                'css' => ['assets/app.css'],
            ],
        ], JSON_THROW_ON_ERROR));

        $container = new Container();
        $app = new Application($basePath, $container, new Repository([
            'assets.manifest' => 'tests/tmp/helper-assets/public/build/.vite/manifest.json',
            'assets.hot_file' => 'tests/tmp/helper-assets/public/hot',
        ]));
        $container->singleton(AssetManager::class);
        $GLOBALS['wtd_app'] = $app;

        try {
            self::assertStringContainsString('/build/assets/app.css', vite('resources/js/app.js'));
            self::assertStringContainsString('/build/assets/app.js', vite(['resources/js/app.js']));
        } finally {
            unset($GLOBALS['wtd_app']);
            @unlink($basePath . '/tests/tmp/helper-assets/public/build/.vite/manifest.json');
            @rmdir($basePath . '/tests/tmp/helper-assets/public/build/.vite');
            @rmdir($basePath . '/tests/tmp/helper-assets/public/build');
            @rmdir($basePath . '/tests/tmp/helper-assets/public');
            @rmdir($basePath . '/tests/tmp/helper-assets');
        }
    }

    public function testSessionCacheAndCookieHelpersExposeFrameworkServices(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $basePath = dirname(__DIR__, 2);
        $container = new Container();
        $app = new Application($basePath, $container, new Repository());
        $session = new SessionStore(new Filesystem(), $basePath . '/tests/tmp/helper-sessions');
        $cache = new CacheRepository(new \WTD\Cache\FileStore());
        $manager = new CacheManager('file');

        $session->start('helper-session-1234');
        $session->put('notice', 'Saved');
        $cache->put('framework', 'WTD', 60);

        $container->instance(SessionStore::class, $session);
        $container->instance(CacheRepository::class, $cache);
        $container->instance(CacheManager::class, $manager);
        $GLOBALS['wtd_app'] = $app;

        try {
            self::assertSame($session, session());
            self::assertSame('Saved', session('notice'));
            session(['mode' => 'standard']);
            self::assertSame('standard', session('mode'));
            self::assertSame($cache, cache());
            self::assertSame('WTD', cache('framework'));
            cache(['runtime' => 'ready'], ttlSeconds: 60);
            self::assertSame('ready', cache('runtime'));
            self::assertInstanceOf(CacheRepository::class, cache_store());
            self::assertInstanceOf(Cookie::class, cookie('theme', 'dark', 5));
            self::assertStringContainsString('theme=dark', cookie('theme', 'dark', 5)->toHeader());
            self::assertStringContainsString('Expires=', forget_cookie('theme')->toHeader());
        } finally {
            unset($GLOBALS['wtd_app']);
            @unlink($basePath . '/tests/tmp/helper-sessions/helper-session-1234');
            @rmdir($basePath . '/tests/tmp/helper-sessions');
        }
    }

    public function testHttpHelperReturnsFrameworkHttpClient(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $basePath = dirname(__DIR__, 2);
        $container = new Container();
        $client = new HttpClient();
        $app = new Application($basePath, $container, new Repository());
        $container->instance(HttpClient::class, $client);
        $GLOBALS['wtd_app'] = $app;

        try {
            self::assertSame($client, http());
        } finally {
            unset($GLOBALS['wtd_app']);
        }
    }

    public function testHookHelpersProxyToHookManager(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $basePath = dirname(__DIR__, 2);
        $container = new Container();
        $hooks = new HookManager();
        $app = new Application($basePath, $container, new Repository());
        $container->instance(HookManager::class, $hooks);
        $GLOBALS['wtd_app'] = $app;
        $calls = [];

        try {
            add_action('helper.action', static function (string $value) use (&$calls): void {
                $calls[] = $value;
            });
            add_filter('helper.filter', static fn (string $value): string => $value . '-filtered');

            do_action('helper.action', 'called');

            self::assertSame($hooks, app_hooks());
            self::assertSame(['called'], $calls);
            self::assertSame('value-filtered', apply_filters('helper.filter', 'value'));
        } finally {
            unset($GLOBALS['wtd_app']);
        }
    }
}
