<?php

declare(strict_types=1);

namespace Tests\Database;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WTD\Config\Repository;
use WTD\Database\Blueprint;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\Factory;
use WTD\Database\Schema;

final class FactoryTest extends TestCase
{
    public function testFactoryMakesRecords(): void
    {
        $records = (new UserFactory())->count(2)->make();

        self::assertSame([
            ['name' => 'User 1'],
            ['name' => 'User 2'],
        ], $records);
    }

    public function testFactoryCreatesRecords(): void
    {
        $connection = $this->connection();
        $schema = new Schema($connection);
        $schema->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        $records = (new UserFactory())->count(2)->create($connection);
        $users = $connection->table('users')->select('name')->get();

        self::assertSame([
            ['name' => 'User 1'],
            ['name' => 'User 2'],
        ], $records);
        self::assertSame($records, $users);
    }

    public function testFactoryRejectsInvalidCount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new UserFactory())->count(0);
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

final class UserFactory extends Factory
{
    protected function table(): string
    {
        return 'users';
    }

    protected function definition(int $sequence): array
    {
        return ['name' => 'User ' . $sequence];
    }
}
