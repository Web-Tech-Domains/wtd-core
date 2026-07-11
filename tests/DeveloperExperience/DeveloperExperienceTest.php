<?php

declare(strict_types=1);

namespace Tests\DeveloperExperience;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Config\Repository;
use WTD\Console\ConsoleServiceProvider;
use WTD\Console\Input;
use WTD\Console\Kernel;
use WTD\Console\Output;
use WTD\Container\Container;
use WTD\Database\DatabaseServiceProvider;
use WTD\DeveloperExperience\DebugToolbarMiddleware;
use WTD\DeveloperExperience\DeveloperExperienceServiceProvider;
use WTD\DeveloperExperience\ErrorPageRenderer;
use WTD\DeveloperExperience\Profiler;
use WTD\Exception\ExceptionRenderer;
use WTD\Http\HttpServiceProvider;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Kernel\HttpKernel;
use WTD\Logging\Logger;
use WTD\Routing\Router;
use WTD\Scheduler\SchedulerServiceProvider;

final class DeveloperExperienceTest extends TestCase
{
    private bool $registeredHandlers = false;

    protected function tearDown(): void
    {
        if ($this->registeredHandlers) {
            restore_error_handler();
            restore_exception_handler();
        }

        $this->removeDirectory(dirname(__DIR__) . '/tmp/dx');
    }

    public function testProfilerMeasuresRuntimeSnapshots(): void
    {
        $profiler = new Profiler();
        $profiler->mark('test');
        $measurement = $profiler->measure(static fn (): string => 'ok');
        $snapshot = $profiler->snapshot();

        self::assertSame('ok', $measurement['result']);
        self::assertGreaterThanOrEqual(0.0, $measurement['elapsed_ms']);
        self::assertGreaterThanOrEqual(0.0, $snapshot['elapsed_ms']);
        self::assertSame('test', $snapshot['marks'][0]['name']);
    }

    public function testDebugToolbarDecoratesHtmlResponsesWhenEnabled(): void
    {
        $app = $this->application([
            'developer.enabled' => true,
            'developer.debug_toolbar' => true,
            'http.middleware' => [DebugToolbarMiddleware::class],
        ]);
        $this->registerHttpApp($app);

        /** @var Router $router */
        $router = $app->container()->get(Router::class);
        $router->get('/toolbar', static fn (): Response => Response::make('<h1>Toolbar</h1>'));
        $app->boot();

        /** @var HttpKernel $kernel */
        $kernel = $app->container()->get(HttpKernel::class);
        $response = $kernel->handle(new Request('GET', '/toolbar'));

        self::assertStringContainsString('wtd-debug-toolbar', $response->content());
        self::assertStringContainsString('aria-label="WTD debug toolbar"', $response->content());
        self::assertStringContainsString('<details>', $response->content());
        self::assertStringContainsString('Profiler Marks', $response->content());
        self::assertStringContainsString('request.start', $response->content());
        self::assertArrayHasKey('X-WTD-Profile-Time', $response->headers());
    }

    public function testOpenApiAndApiDocumentationRoutesCanBeExposed(): void
    {
        $app = $this->application([
            'developer.api_docs' => true,
        ]);
        $this->registerHttpApp($app);

        /** @var Router $router */
        $router = $app->container()->get(Router::class);
        $router->get('/users/{id}', static fn (): array => ['id' => 1])->name('users.show');
        $app->boot();

        /** @var HttpKernel $kernel */
        $kernel = $app->container()->get(HttpKernel::class);
        $openApi = $kernel->handle(new Request('GET', '/docs/openapi.json'));
        $docs = $kernel->handle(new Request('GET', '/docs/api'));

        self::assertSame('application/json', $openApi->headers()['Content-Type']);
        self::assertStringContainsString('"openapi":"3.1.0"', $openApi->content());
        self::assertStringContainsString('"users.show"', $openApi->content());
        self::assertStringContainsString('API Documentation', $docs->content());
    }

    public function testErrorPagesCanRenderDebugHtml(): void
    {
        $renderer = new ExceptionRenderer(
            new Repository([
                'app.debug' => true,
                'developer.error_pages' => true,
            ]),
            new Logger(dirname(__DIR__) . '/tmp/logs/developer-experience.log'),
            new ErrorPageRenderer(),
        );

        $response = $renderer->render(new RuntimeException('Debug failure'));

        self::assertSame(500, $response->status());
        self::assertStringContainsString('<!doctype html>', $response->content());
        self::assertStringContainsString('Debug failure', $response->content());
        self::assertStringContainsString('WTD Core', $response->content());
        self::assertStringContainsString('class="topbar"', $response->content());
        self::assertStringContainsString('Back to Home', $response->content());
        self::assertStringContainsString('Debug details', $response->content());
    }

    public function testDeveloperExperienceCliCommands(): void
    {
        $app = $this->application([
            'developer.api_docs' => true,
            'developer.benchmark_iterations' => 2,
        ]);
        $this->registerFullApp($app);

        /** @var Router $router */
        $router = $app->container()->get(Router::class);
        $router->get('/', static fn (): Response => Response::make('Home'))->name('home');
        $app->boot();

        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);

        self::assertArrayHasKey('api:docs', $kernel->commands());
        self::assertArrayHasKey('benchmark', $kernel->commands());
        self::assertArrayHasKey('ide:helper', $kernel->commands());
        self::assertArrayHasKey('make:resource', $kernel->commands());

        [$output, $stdout] = $this->consoleOutput();

        self::assertSame(0, $kernel->handle(new Input([
            'api:docs',
            '--path=tests/tmp/dx/openapi.json',
        ]), $output));
        self::assertSame(0, $kernel->handle(new Input([
            'ide:helper',
            '--path=tests/tmp/dx/_ide_helper.php',
        ]), $output));
        self::assertSame(0, $kernel->handle(new Input([
            'make:resource',
            'Post',
            '--model=Post',
            '--path=tests/tmp/dx/PostController.php',
        ]), $output));
        self::assertSame(0, $kernel->handle(new Input([
            'benchmark',
            '/',
            '--iterations=2',
        ]), $output));

        self::assertFileExists(dirname(__DIR__) . '/tmp/dx/openapi.json');
        self::assertFileExists(dirname(__DIR__) . '/tmp/dx/_ide_helper.php');
        self::assertFileExists(dirname(__DIR__) . '/tmp/dx/PostController.php');

        rewind($stdout);
        $contents = (string) stream_get_contents($stdout);
        self::assertStringContainsString('OpenAPI documentation written', $contents);
        self::assertStringContainsString('IDE helper written', $contents);
        self::assertStringContainsString('Resource controller created', $contents);
        self::assertStringContainsString('"iterations": 2', $contents);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function application(array $config = []): Application
    {
        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        return new Application(
            $basePath,
            new Container(),
            new Repository(array_replace([
                'app.name' => 'Developer Experience Test',
                'app.debug' => false,
                'developer.enabled' => false,
                'developer.api_docs' => false,
                'developer.debug_toolbar' => false,
                'developer.error_pages' => true,
                'developer.benchmark_iterations' => 2,
                'http.middleware' => [],
                'database.default' => 'sqlite',
                'database.connections.sqlite.driver' => 'sqlite',
                'database.connections.sqlite.database' => ':memory:',
            ], $config)),
        );
    }

    private function registerHttpApp(Application $app): void
    {
        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DeveloperExperienceServiceProvider::class);
        $this->registeredHandlers = true;
    }

    private function registerFullApp(Application $app): void
    {
        $app->register(CoreServiceProvider::class);
        $app->register(ConsoleServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DatabaseServiceProvider::class);
        $app->register(SchedulerServiceProvider::class);
        $app->register(DeveloperExperienceServiceProvider::class);
        $this->registeredHandlers = true;
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
