<?php

declare(strict_types=1);

namespace Tests\Database;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Database\Blueprint;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\DatabaseServiceProvider;
use WTD\Database\Schema;

final class SchemaTest extends TestCase
{
    public function testSchemaCanCreateAndDropTables(): void
    {
        $schema = $this->schema();

        self::assertFalse($schema->hasTable('users'));

        $schema->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->nullableString('email');
            $table->boolean('active');
            $table->timestamps();
        });

        self::assertTrue($schema->hasTable('users'));

        $schema->dropIfExists('users');

        self::assertFalse($schema->hasTable('users'));
    }

    public function testSchemaCreatedTablesAcceptRows(): void
    {
        $connection = (new DatabaseManager($this->config()))->connection();
        $schema = new Schema($connection);

        $schema->create('posts', static function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('body');
        });

        $connection->statement('INSERT INTO posts (title, body) VALUES (?, ?)', ['Hello', 'Body']);

        self::assertSame([['title' => 'Hello']], $connection->select('SELECT title FROM posts'));
    }

    public function testDatabaseServiceProviderRegistersSchema(): void
    {
        /** @var non-empty-string $basePath */
        $basePath = dirname(__DIR__, 2);
        $app = new Application($basePath, new Container(), $this->config());
        $app->register(DatabaseServiceProvider::class);

        self::assertInstanceOf(Schema::class, $app->container()->get(Schema::class));
    }

    private function schema(): Schema
    {
        return new Schema((new DatabaseManager($this->config()))->connection());
    }

    private function config(): Repository
    {
        return new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
    }
}
