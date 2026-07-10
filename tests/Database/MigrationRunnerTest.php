<?php

declare(strict_types=1);

namespace Tests\Database;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Config\Repository;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\MigrationRepository;
use WTD\Database\MigrationRunner;
use WTD\Database\Schema;

final class MigrationRunnerTest extends TestCase
{
    public function testMigrationRunnerRunsPendingMigrations(): void
    {
        [$runner, $schema, $repository] = $this->runner('run');

        $ran = $runner->migrate();

        self::assertSame(['2026_01_01_000000_create_widgets_table'], $ran);
        self::assertTrue($schema->hasTable('widgets'));
        self::assertSame(['2026_01_01_000000_create_widgets_table'], $repository->ran());
        self::assertSame([], $runner->migrate());
    }

    public function testMigrationRunnerRollsBackLatestBatch(): void
    {
        [$runner, $schema] = $this->runner('rollback');

        $runner->migrate();

        self::assertTrue($schema->hasTable('widgets'));

        $rolledBack = $runner->rollback();

        self::assertSame(['2026_01_01_000000_create_widgets_table'], $rolledBack);
        self::assertFalse($schema->hasTable('widgets'));
        self::assertSame([], $runner->rollback());
    }

    public function testMigrationRunnerRejectsInvalidMigrationFiles(): void
    {
        [$runner] = $this->runner('invalid', invalid: true);

        $this->expectException(RuntimeException::class);

        $runner->migrate();
    }

    /**
     * @return array{0: MigrationRunner, 1: Schema, 2: MigrationRepository}
     */
    private function runner(string $name, bool $invalid = false): array
    {
        $path = dirname(__DIR__) . '/tmp/migrations/' . $name;

        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        $migrationPath = $path . '/2026_01_01_000000_create_widgets_table.php';
        $contents = $invalid
            ? "<?php\n\ndeclare(strict_types=1);\n\nreturn new stdClass();\n"
            : <<<'PHP'
<?php

declare(strict_types=1);

use WTD\Database\Blueprint;
use WTD\Database\Migration;
use WTD\Database\Schema;

return new class implements Migration {
    public function up(Schema $schema): void
    {
        $schema->create('widgets', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('widgets');
    }
};
PHP;

        file_put_contents($migrationPath, $contents);

        $connection = $this->connection();
        $schema = new Schema($connection);
        $repository = new MigrationRepository($connection);

        return [new MigrationRunner($repository, $schema, $path), $schema, $repository];
    }

    private function connection(): Connection
    {
        return (new DatabaseManager(new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ])))->connection();
    }
}
