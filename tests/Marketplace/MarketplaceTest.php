<?php

declare(strict_types=1);

namespace Tests\Marketplace;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Config\Repository;
use WTD\Console\ConsoleServiceProvider;
use WTD\Console\Input;
use WTD\Console\Kernel;
use WTD\Console\Output;
use WTD\Container\Container;
use WTD\Database\DatabaseServiceProvider;
use WTD\Filesystem\Filesystem;
use WTD\Http\HttpServiceProvider;
use WTD\Marketplace\MarketplaceRegistry;
use WTD\Marketplace\MarketplaceServiceProvider;
use WTD\Marketplace\PackageInstaller;
use WTD\Scheduler\SchedulerServiceProvider;

final class MarketplaceTest extends TestCase
{
    private bool $registeredHandlers = false;

    protected function tearDown(): void
    {
        $this->removeDirectory(dirname(__DIR__) . '/tmp/marketplace');

        if ($this->registeredHandlers) {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function testMarketplaceDiscoversAndInstallsLocalPackages(): void
    {
        $this->createPackageFixture();
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(MarketplaceServiceProvider::class);

        /** @var MarketplaceRegistry $registry */
        $registry = $app->container()->get(MarketplaceRegistry::class);
        /** @var PackageInstaller $installer */
        $installer = $app->container()->get(PackageInstaller::class);

        $packages = $registry->all();
        self::assertCount(1, $packages);
        self::assertSame('wtd/example', $packages[0]->name);

        $installed = $installer->install('wtd/example');

        self::assertSame('1.0.0', $installed->version);
        self::assertArrayHasKey('wtd/example', $installer->installed());
        self::assertSame([ExamplePackageProvider::class], $installer->installedProviders());
    }

    public function testMarketplacePublishesPackageConfiguration(): void
    {
        $this->createPackageFixture();
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(MarketplaceServiceProvider::class);

        /** @var PackageInstaller $installer */
        $installer = $app->container()->get(PackageInstaller::class);
        $published = $installer->publish('wtd/example');

        self::assertCount(1, $published);
        self::assertFileExists(dirname(__DIR__) . '/tmp/marketplace/published/example.php');
        self::assertStringContainsString("'enabled' => true", (string) file_get_contents(dirname(__DIR__) . '/tmp/marketplace/published/example.php'));
    }

    public function testMarketplaceCommandsListInstallAndPublishPackages(): void
    {
        $this->createPackageFixture();
        $app = $this->application();
        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DatabaseServiceProvider::class);
        $app->register(SchedulerServiceProvider::class);
        $app->register(ConsoleServiceProvider::class);
        $app->register(MarketplaceServiceProvider::class);
        $this->registeredHandlers = true;
        $app->boot();

        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);
        [$output, $stdout] = $this->consoleOutput();

        self::assertArrayHasKey('marketplace:list', $kernel->commands());
        self::assertArrayHasKey('marketplace:install', $kernel->commands());
        self::assertArrayHasKey('marketplace:publish', $kernel->commands());

        self::assertSame(0, $kernel->handle(new Input(['marketplace:list']), $output));
        self::assertSame(0, $kernel->handle(new Input(['marketplace:install', 'wtd/example']), $output));
        self::assertSame(0, $kernel->handle(new Input(['marketplace:publish', 'wtd/example']), $output));

        rewind($stdout);
        $contents = (string) stream_get_contents($stdout);
        self::assertStringContainsString('wtd/example 1.0.0 [available]', $contents);
        self::assertStringContainsString('Package installed: wtd/example', $contents);
        self::assertStringContainsString('Published:', $contents);
    }

    private function createPackageFixture(): void
    {
        $files = new Filesystem();
        $root = dirname(__DIR__) . '/tmp/marketplace/packages/example';
        $files->put($root . '/wtd-package.php', <<<PHP
<?php

return [
    'name' => 'wtd/example',
    'version' => '1.0.0',
    'description' => 'Example package.',
    'providers' => [
        Tests\\Marketplace\\ExamplePackageProvider::class,
    ],
    'config' => [
        'example/config/example.php' => 'tests/tmp/marketplace/published/example.php',
    ],
    'keywords' => ['example'],
];
PHP);
        $files->put($root . '/config/example.php', "<?php\n\nreturn ['enabled' => true];\n");
    }

    private function application(): Application
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        return new Application(
            $basePath,
            new Container(),
            new Repository([
                'app.name' => 'Marketplace Test',
                'marketplace.paths.packages' => 'tests/tmp/marketplace/packages',
                'marketplace.paths.installed' => 'tests/tmp/marketplace/installed.php',
                'marketplace.auto_register' => false,
                'database.default' => 'sqlite',
                'database.connections.sqlite.driver' => 'sqlite',
                'database.connections.sqlite.database' => ':memory:',
            ]),
        );
    }

    /**
     * @return array{0: Output, 1: resource}
     */
    private function consoleOutput(): array
    {
        $stdout = fopen('php://temp', 'r+');
        $stderr = fopen('php://temp', 'r+');

        self::assertIsResource($stdout);
        self::assertIsResource($stderr);

        return [new Output($stdout, $stderr), $stdout];
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($directory);
    }
}

final class ExamplePackageProvider extends \WTD\Support\ServiceProvider
{
}
