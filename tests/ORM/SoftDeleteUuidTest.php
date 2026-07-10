<?php

declare(strict_types=1);

namespace Tests\ORM;

use PHPUnit\Framework\TestCase;
use WTD\Config\Repository;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\ORM\Model;

final class SoftDeleteUuidTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = (new DatabaseManager(new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ])))->connection();

        SoftUser::setConnection($this->connection);
        UuidUser::setConnection($this->connection);

        $this->connection->statement('CREATE TABLE soft_users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, deleted_at DATETIME NULL)');
        $this->connection->statement('CREATE TABLE uuid_users (id VARCHAR(36) PRIMARY KEY, name TEXT NOT NULL)');
    }

    public function testSoftDeletedModelsAreHiddenAndCanBeRestored(): void
    {
        $user = new SoftUser(['name' => 'Taylor']);
        $user->save();

        self::assertTrue($user->delete());
        self::assertNull(SoftUser::find(1));
        self::assertSame(1, SoftUser::withTrashed()->count());
        self::assertSame(1, SoftUser::onlyTrashed()->count());

        $trashed = SoftUser::withTrashed()->where('id', 1)->first();

        self::assertInstanceOf(SoftUser::class, $trashed);
        self::assertTrue($trashed->restore());
        self::assertInstanceOf(SoftUser::class, SoftUser::find(1));
        self::assertSame(0, SoftUser::onlyTrashed()->count());
    }

    public function testUuidModelsAssignPrimaryKeysBeforeInsert(): void
    {
        $user = new UuidUser(['name' => 'Taylor']);

        self::assertTrue($user->save());
        self::assertIsString($user->getKey());
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', (string) $user->getKey());
        self::assertInstanceOf(UuidUser::class, UuidUser::find($user->getKey()));
    }
}

final class SoftUser extends Model
{
    protected ?string $table = 'soft_users';

    protected bool $softDeletes = true;
}

final class UuidUser extends Model
{
    protected ?string $table = 'uuid_users';

    protected bool $usesUuids = true;
}
