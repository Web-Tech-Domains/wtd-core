<?php

declare(strict_types=1);

namespace Tests\ORM;

use PHPUnit\Framework\TestCase;
use WTD\Config\Repository;
use WTD\Database\Blueprint;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\Schema;
use WTD\ORM\Model;
use WTD\ORM\ModelRepository;

final class ModelRepositoryTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = (new DatabaseManager(new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ])))->connection();
        RepositoryUser::setConnection($this->connection);

        $schema = new Schema($this->connection);
        $schema->create('repository_users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
    }

    public function testRepositoryCreatesFindsListsSavesAndDeletesModels(): void
    {
        $repository = new ModelRepository(RepositoryUser::class);
        $user = $repository->create(['name' => 'Taylor']);

        self::assertInstanceOf(RepositoryUser::class, $user);
        self::assertSame('Taylor', $repository->find(1)?->getAttribute('name'));
        self::assertCount(1, $repository->all());

        $user->setAttribute('name', 'Updated');

        self::assertTrue($repository->save($user));
        self::assertSame('Updated', $repository->find(1)->getAttribute('name'));
        self::assertTrue($repository->delete($user));
        self::assertNull($repository->find(1));
    }
}

final class RepositoryUser extends Model
{
    protected ?string $table = 'repository_users';
}
