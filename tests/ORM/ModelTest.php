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
        OrmUser::flushModelEvents();
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

    public function testModelCastsAttributesAndUsesAccessorsAndMutators(): void
    {
        CastUser::setConnection($this->connection);
        $schema = new Schema($this->connection);
        $schema->create('cast_users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('active');
            $table->text('settings');
        });

        $user = new CastUser([
            'name' => 'taylor',
            'active' => '1',
            'settings' => '{"theme":"dark"}',
        ]);

        self::assertTrue($user->save());

        $found = CastUser::find(1);

        self::assertInstanceOf(CastUser::class, $found);
        self::assertSame('TAYLOR', $found->getAttribute('name'));
        self::assertTrue($found->getAttribute('active'));
        self::assertSame(['theme' => 'dark'], $found->getAttribute('settings'));
        self::assertSame('taylor', $found->attributes()['name']);
    }

    public function testModelEventsAndObserversRunForLifecycleActions(): void
    {
        $events = [];
        $observer = new UserObserver($events);
        OrmUser::registerModelEvent('saving', static function (Model $user) use (&$events): void {
            $events[] = 'listener:saving:' . $user->getAttribute('name');
        });
        OrmUser::observe($observer);

        $user = new OrmUser(['name' => 'Taylor', 'active' => 1]);
        $user->save();
        $user->setAttribute('name', 'Updated');
        $user->save();
        $user->delete();

        self::assertSame([
            'listener:saving:Taylor',
            'observer:creating:Taylor',
            'observer:created:Taylor',
            'listener:saving:Updated',
            'observer:updated:Updated',
            'observer:deleted:Updated',
        ], $events);
        self::assertSame($events, $observer->events());
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

final class CastUser extends Model
{
    protected ?string $table = 'cast_users';

    /**
     * @var array<string, string>
     */
    protected array $casts = [
        'active' => 'bool',
        'settings' => 'array',
    ];

    public function getNameAttribute(mixed $value): string
    {
        return strtoupper((string) $value);
    }

    public function setNameAttribute(mixed $value): string
    {
        return strtolower((string) $value);
    }
}

final class UserObserver
{
    /**
     * @param list<string> $events
     */
    public function __construct(private array &$events)
    {
    }

    public function creating(OrmUser $user): void
    {
        $this->events[] = 'observer:creating:' . $user->getAttribute('name');
    }

    public function created(OrmUser $user): void
    {
        $this->events[] = 'observer:created:' . $user->getAttribute('name');
    }

    public function updated(OrmUser $user): void
    {
        $this->events[] = 'observer:updated:' . $user->getAttribute('name');
    }

    public function deleted(OrmUser $user): void
    {
        $this->events[] = 'observer:deleted:' . $user->getAttribute('name');
    }

    /**
     * @return list<string>
     */
    public function events(): array
    {
        return $this->events;
    }
}
