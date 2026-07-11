<?php

declare(strict_types=1);

namespace Tests\Documentation;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Application\CoreServiceProvider;
use WTD\Config\Repository;
use WTD\Console\ConsoleServiceProvider;
use WTD\Console\Kernel;
use WTD\Container\Container;
use WTD\Database\DatabaseServiceProvider;
use WTD\Http\HttpServiceProvider;
use WTD\Scheduler\SchedulerServiceProvider;

final class DocumentationTest extends TestCase
{
    public function testRequiredDocumentationExists(): void
    {
        foreach ($this->requiredDocs() as $doc) {
            self::assertFileExists($this->docsPath($doc));
        }
    }

    public function testDocumentationLinksResolve(): void
    {
        foreach ($this->markdownFiles() as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);

            preg_match_all('/\[[^\]]+\]\(([^)#][^)]+\.md)\)/', $contents, $matches);

            foreach ($matches[1] as $target) {
                self::assertFileExists(dirname($file) . DIRECTORY_SEPARATOR . $target, $file . ' links to missing doc ' . $target);
            }
        }
    }

    public function testCliReferenceDocumentsEveryBuiltInCommand(): void
    {
        $reference = file_get_contents($this->docsPath('CLI_REFERENCE.md'));
        self::assertIsString($reference);

        $basePath = dirname(__DIR__, 2);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application(
            $basePath,
            new Container(),
            new Repository([
                'app.name' => 'Documentation Test',
                'database.default' => 'sqlite',
                'database.connections.sqlite.driver' => 'sqlite',
                'database.connections.sqlite.database' => ':memory:',
            ]),
        );

        $app->register(CoreServiceProvider::class);
        $app->register(HttpServiceProvider::class);
        $app->register(DatabaseServiceProvider::class);
        $app->register(SchedulerServiceProvider::class);
        $app->register(ConsoleServiceProvider::class);

        /** @var Kernel $kernel */
        $kernel = $app->container()->get(Kernel::class);

        foreach (array_keys($kernel->commands()) as $command) {
            self::assertStringContainsString('| `' . $command . '` |', $reference);
        }
    }

    /**
     * @return list<string>
     */
    private function requiredDocs(): array
    {
        return [
            'README.md',
            'GETTING_STARTED.md',
            'ARCHITECTURE.md',
            'CLI_REFERENCE.md',
            'HTTP.md',
            'DATABASE_ORM.md',
            'SECURITY.md',
            'OPERATIONS.md',
            'DEVELOPER_EXPERIENCE.md',
            'MARKETPLACE.md',
            'PRODUCT_BLUEPRINT.md',
            'SOFTWARE_ARCHITECTURE_SPECIFICATION.md',
        ];
    }

    /**
     * @return list<string>
     */
    private function markdownFiles(): array
    {
        $files = glob($this->docsPath('*.md'));
        self::assertIsArray($files);

        return array_values($files);
    }

    private function docsPath(string $file): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . $file;
    }
}
