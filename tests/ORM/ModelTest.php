<?php

declare(strict_types=1);

namespace Tests\ORM;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Config\Repository;
use WTD\Database\Blueprint;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\Schema;
use WTD\ORM\Model;

final class ModelTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = $this->makeConnection();
        OrmUser::setConnection($this->connection);
        $schema = new Schema($this->connection);
        $schema->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('active');
        });
    }

    public function testModelCreatesAndFindsRows(): void
    {
        $user = new OrmUser(['name' => 'Taylor', 'active' => 1]);

        self::assertTrue($user->save());
        self::assertTrue($user->exists());
        self::assertSame(1, $user->getAttribute('id'));

        $found = OrmUser::find(1);

        self::assertInstanceOf(OrmUser::class, $found);
        self::assertSame('Taylor', $found->getAttribute('name'));
        self::assertTrue($found->exists());
    }

    public function testModelListsUpdatesAndDeletesRows(): void
    {
        (new OrmUser(['name' => 'Taylor', 'active' => 1]))->save();
        (new OrmUser(['name' => 'Ada', 'active' => 0]))->save();

        $users = OrmUser::all();
        $users[0]->setAttribute('name', 'Updated');

        self::assertCount(2, $users);
        self::assertTrue($users[0]->save());
        self::assertSame('Updated', OrmUser::find(1)?->getAttribute('name'));
        self::assertTrue($users[0]->delete());
        self::assertNull(OrmUser::find(1));
    }

    public function testModelRequiresConfiguredConnection(): void
    {
        UnconfiguredModel::setConnection(null);

        $this->expectException(RuntimeException::class);

        UnconfiguredModel::all();
    }

    private function makeConnection(): Connection
    {
        return (new DatabaseManager(new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ])))->connection();
    }
}

final class OrmUser extends Model
{
    protected ?string $table = 'users';
}

final class UnconfiguredModel extends Model
{
}
