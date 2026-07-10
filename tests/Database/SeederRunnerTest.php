<?php

declare(strict_types=1);

namespace Tests\Database;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Config\Repository;
use WTD\Database\Blueprint;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\Schema;
use WTD\Database\SeederRunner;

final class SeederRunnerTest extends TestCase
{
    public function testSeederRunnerRunsAllSeeders(): void
    {
        [$runner, $connection] = $this->runner('all', [
            'UserSeeder' => 'first-user',
            'AdminSeeder' => 'second-user',
        ]);

        $ran = $runner->run();
        $users = $connection->table('users')->select('name')->get();

        self::assertSame(['AdminSeeder', 'UserSeeder'], $ran);
        self::assertSame([['name' => 'second-user'], ['name' => 'first-user']], $users);
    }

    public function testSeederRunnerRunsNamedSeeder(): void
    {
        [$runner, $connection] = $this->runner('named', [
            'AlphaSeeder' => 'alpha',
            'BetaSeeder' => 'beta',
        ]);

        $ran = $runner->run('BetaSeeder');
        $users = $connection->table('users')->select('name')->get();

        self::assertSame(['BetaSeeder'], $ran);
        self::assertSame([['name' => 'beta']], $users);
    }

    public function testSeederRunnerRejectsInvalidSeederFiles(): void
    {
        [$runner] = $this->runner('invalid', [], invalid: true);

        $this->expectException(RuntimeException::class);

        $runner->run('InvalidSeeder');
    }

    public function testSeederRunnerRejectsMissingSeeder(): void
    {
        [$runner] = $this->runner('missing', []);

        $this->expectException(RuntimeException::class);

        $runner->run('MissingSeeder');
    }

    /**
     * @param array<string, string> $seeders
     *
     * @return array{0: SeederRunner, 1: Connection}
     */
    private function runner(string $name, array $seeders, bool $invalid = false): array
    {
        $path = dirname(__DIR__) . '/tmp/seeders/' . $name;

        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        foreach ($seeders as $seeder => $userName) {
            file_put_contents($path . '/' . $seeder . '.php', $this->seederContents($userName));
        }

        if ($invalid) {
            file_put_contents($path . '/InvalidSeeder.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn new stdClass();\n");
        }

        $connection = $this->connection();
        $schema = new Schema($connection);
        $schema->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        return [new SeederRunner($connection, $path), $connection];
    }

    private function seederContents(string $name): string
    {
        return sprintf(<<<'PHP'
<?php

declare(strict_types=1);

use WTD\Database\Connection;
use WTD\Database\Seeder;

return new class implements Seeder {
    public function run(Connection $connection): void
    {
        $connection->table('users')->insert(['name' => '%s']);
    }
};
PHP, $name);
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
