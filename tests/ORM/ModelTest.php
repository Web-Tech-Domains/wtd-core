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
use WTD\Application\Application;
use WTD\Container\Container;
use WTD\Hooks\HookManager;
use WTD\ORM\Model;

final class ModelTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        Model::setDatabaseManager(null);
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

    public function testModelQueryBuilderOrdersAndLimitsRows(): void
    {
        (new OrmUser(['name' => 'Taylor', 'active' => 1]))->save();
        (new OrmUser(['name' => 'Ada', 'active' => 1]))->save();
        (new OrmUser(['name' => 'Grace', 'active' => 0]))->save();

        $users = OrmUser::query()
            ->orderByDesc('active')
            ->orderBy('name')
            ->take(2)
            ->get();

        self::assertSame('Ada', $users[0]->getAttribute('name'));
        self::assertSame('Taylor', $users[1]->getAttribute('name'));
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

    public function testModelTimestampsCallbacksAndFieldProtection(): void
    {
        TimestampedUser::setConnection($this->connection);
        $schema = new Schema($this->connection);
        $schema->create('timestamped_users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $user = new TimestampedUser(['name' => 'Taylor', 'admin' => true]);

        self::assertTrue($user->save());

        $row = $this->connection->table('timestamped_users')->first();

        self::assertNotNull($row);
        self::assertSame('before-Taylor', $row['name']);
        self::assertArrayHasKey('created_at', $row);
        self::assertArrayHasKey('updated_at', $row);
        self::assertArrayNotHasKey('admin', $row);
        self::assertSame(['beforeInsert', 'afterInsert'], $user->callbacks);

        $user->setAttribute('name', 'Updated');

        self::assertTrue($user->save());

        $updated = $this->connection->table('timestamped_users')->where('id', 1)->first();

        self::assertNotNull($updated);
        self::assertSame('before-Updated', $updated['name']);
        self::assertSame(['beforeInsert', 'afterInsert', 'beforeUpdate', 'afterUpdate'], $user->callbacks);
    }

    public function testModelCanUseNamedDatabaseManagerConnection(): void
    {
        OrmUser::setConnection(null);
        NamedConnectionUser::setConnection(null);
        $manager = new DatabaseManager(new Repository([
            'database.default' => 'primary',
            'database.connections.primary.driver' => 'sqlite',
            'database.connections.primary.database' => ':memory:',
            'database.connections.reporting.driver' => 'sqlite',
            'database.connections.reporting.database' => ':memory:',
        ]));
        Model::setDatabaseManager($manager);

        $schema = new Schema($manager->connection('reporting'));
        $schema->create('named_users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        $user = new NamedConnectionUser(['name' => 'Report']);

        self::assertTrue($user->save());
        self::assertSame([['name' => 'Report']], $manager->connection('reporting')->table('named_users')->select('name')->get());
    }

    public function testModelFiresCompatibilityDataHooks(): void
    {
        require_once dirname(__DIR__, 2) . '/system/Support/helpers.php';

        $container = new Container();
        $app = new Application(dirname(__DIR__, 2), $container, new Repository());
        $container->instance(HookManager::class, new HookManager());
        $GLOBALS['wtd_app'] = $app;
        $events = [];

        try {
            register_data_insert_hook(static function (array $data) use (&$events): void {
                $events[] = 'insert:' . $data['table_without_prefix'] . ':' . $data['attributes']['name'];
            });
            register_data_update_hook(static function (array $data) use (&$events): void {
                $events[] = 'update:' . $data['table_without_prefix'] . ':' . $data['attributes']['name'];
            });
            register_data_delete_hook(static function (array $data) use (&$events): void {
                $events[] = 'delete:' . $data['table_without_prefix'] . ':' . $data['id'];
            });

            $user = new OrmUser(['name' => 'Taylor', 'active' => 1]);
            $user->save();
            $user->setAttribute('name', 'Updated');
            $user->save();
            $user->delete();

            self::assertSame([
                'insert:users:Taylor',
                'update:users:Updated',
                'delete:users:1',
            ], $events);
        } finally {
            unset($GLOBALS['wtd_app']);
        }
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

final class TimestampedUser extends Model
{
    protected ?string $table = 'timestamped_users';

    protected bool $useTimestamps = true;

    protected bool $protectFields = true;

    /**
     * @var list<string>
     */
    protected array $allowedFields = ['name'];

    /**
     * @var list<string>
     */
    protected array $beforeInsert = ['beforeInsertHook'];

    /**
     * @var list<string>
     */
    protected array $afterInsert = ['afterInsertHook'];

    /**
     * @var list<string>
     */
    protected array $beforeUpdate = ['beforeUpdateHook'];

    /**
     * @var list<string>
     */
    protected array $afterUpdate = ['afterUpdateHook'];

    /**
     * @var list<string>
     */
    public array $callbacks = [];

    protected function beforeInsertHook(): void
    {
        $this->callbacks[] = 'beforeInsert';
        $this->setAttribute('name', 'before-' . $this->getAttribute('name'));
    }

    protected function afterInsertHook(): void
    {
        $this->callbacks[] = 'afterInsert';
    }

    protected function beforeUpdateHook(): void
    {
        $this->callbacks[] = 'beforeUpdate';
        $this->setAttribute('name', 'before-' . $this->getAttribute('name'));
    }

    protected function afterUpdateHook(): void
    {
        $this->callbacks[] = 'afterUpdate';
    }
}

final class NamedConnectionUser extends Model
{
    protected ?string $connectionName = 'reporting';

    protected ?string $table = 'named_users';
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
