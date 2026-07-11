<?php

declare(strict_types=1);

namespace Tests\Hooks;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Hooks\HookManager;
use WTD\Hooks\HookServiceProvider;
use WTD\Http\Response;

final class HookManagerTest extends TestCase
{
    public function testActionsRunInPriorityOrderWithPayload(): void
    {
        $hooks = new HookManager();
        $calls = [];

        $hooks->addAction('invoice.paid', static function (int $invoiceId) use (&$calls): void {
            $calls[] = 'late:' . $invoiceId;
        }, 20);

        $hooks->addAction('invoice.paid', static function (int $invoiceId) use (&$calls): void {
            $calls[] = 'early:' . $invoiceId;
        }, 5);

        $hooks->doAction('invoice.paid', 42);

        self::assertSame(['early:42', 'late:42'], $calls);
        self::assertTrue($hooks->hasAction('invoice.paid'));
    }

    public function testFiltersTransformValuesInPriorityOrder(): void
    {
        $hooks = new HookManager();

        $hooks->addFilter('content.rendered', static fn (string $content): string => $content . ' second', 20);
        $hooks->addFilter('content.rendered', static fn (string $content): string => $content . ' first', 5);

        self::assertSame('WTD first second', $hooks->applyFilters('content.rendered', 'WTD'));
        self::assertTrue($hooks->hasFilter('content.rendered'));
    }

    public function testSnakeCaseAliasesMatchPluginStyleHookUsage(): void
    {
        $hooks = new HookManager();
        $calls = [];

        $hooks->add_action('plugin.loaded', static function () use (&$calls): void {
            $calls[] = 'loaded';
        });

        $hooks->add_filter('plugin.name', static fn (string $name): string => strtoupper($name));

        $hooks->do_action('plugin.loaded');

        self::assertSame(['loaded'], $calls);
        self::assertSame('BLOG', $hooks->apply_filters('plugin.name', 'blog'));
    }

    public function testServiceProviderLoadsConfiguredHookFiles(): void
    {
        $basePath = dirname(__DIR__, 2);
        $hookFile = $basePath . '/tests/tmp/test-hooks.php';
        file_put_contents($hookFile, <<<'PHP'
<?php

$hooks->addAction('hooks.loaded', static function ($app): void {
    $app->config()->set('hooks.test_loaded', true);
});
PHP);

        $container = new Container();
        $config = new Repository([
            'hooks.enabled' => true,
            'hooks.files' => [$hookFile],
        ]);
        $app = new Application($basePath, $container, $config);

        try {
            $app->register(HookServiceProvider::class);
            $app->boot();

            self::assertTrue($config->get('hooks.test_loaded', false));
        } finally {
            @unlink($hookFile);
        }
    }

    public function testLegacyRedirectHookNameReceivesRedirectPayload(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $basePath = dirname(__DIR__, 2);
        $container = new Container();
        $app = new Application($basePath, $container, new Repository());
        $container->instance(HookManager::class, new HookManager());
        $GLOBALS['wtd_app'] = $app;
        $payload = null;

        try {
            app_hooks()->add_action('app_hook_before_redirect', static function (array $data) use (&$payload): void {
                $payload = $data;
            });

            ob_start();
            Response::redirect('/dashboard')->send();
            ob_end_clean();

            self::assertIsArray($payload);
            self::assertSame(302, $payload['status']);
            self::assertSame('/dashboard', $payload['location']);
            self::assertInstanceOf(Response::class, $payload['response']);
        } finally {
            unset($GLOBALS['wtd_app']);
        }
    }
}
