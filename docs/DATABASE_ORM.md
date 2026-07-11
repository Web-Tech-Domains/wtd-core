# Database and ORM

Database features live in `system/Database`; ORM features live in `system/ORM`.

## Connections

Configure database connections in `config/database.php`. Supported targets include SQLite, MySQL, MariaDB, PostgreSQL, and SQL Server through PDO. Driver names `pgsql`, `postgres`, and `postgresql` are accepted for PostgreSQL, while `mysql` and `mariadb` share the PDO MySQL transport.

Multiple named connections can be defined under `database.connections` and resolved anywhere through `DatabaseManager`.

```php
$default = $database->connection();
$reporting = $database->connection('reporting');
```

Custom database providers can be attached with `DatabaseManager::extend($driver, $resolver)`, allowing package or module code to supply its own connection factory for a driver.

## Query Builder

```php
$users = $db->table('users')
    ->where('active', '=', 1)
    ->orderBy('id')
    ->get();
```

The query layer includes grammar support, pagination, chunking, query events, and identifier validation coverage.

## Schema And Migrations

Migrations implement `WTD\Database\Migration`.

```bash
php core make:migration create_users_table --table=users
php core migrate --database=reporting
php core migrate:rollback --database=reporting
```

The migration runner tracks batches through the migration repository and supports rollback. Schema blueprints include `id()`, typed columns, `timestamps()`, and `softDeletes()`.

## Seeders And Factories

Seeders implement `WTD\Database\Seeder` and can be run with:

```bash
php core make:seeder UserSeeder
php core db:seed UserSeeder --database=reporting
```

Factories extend the database factory base and can build repeatable test or seed data.

## Models

Models extend `WTD\ORM\Model`.

```php
<?php

namespace App\Models;

use WTD\ORM\Model;

final class User extends Model
{
    protected ?string $table = 'users';

    protected ?string $connectionName = 'reporting';

    protected bool $useTimestamps = true;

    protected bool $protectFields = true;

    /**
     * @var list<string>
     */
    protected array $allowedFields = ['name', 'email'];
}
```

The ORM includes local scopes, lifecycle events, observers, soft deletes, UUID primary keys, casts, accessors, mutators, repositories, and HasOne, HasMany, BelongsTo, many-to-many, and polymorphic relationships.

Models can enable automatic `created_at` and `updated_at` values with `$useTimestamps = true`. `$protectFields` and `$allowedFields` prevent unapproved mass-assigned attributes from being written to the database.

Set `$connectionName` on a model to use a named database connection. If it is `null`, the model uses the configured default connection through the shared `DatabaseManager`.

For CodeIgniter-style lifecycle hooks, set `$beforeInsert`, `$afterInsert`, `$beforeUpdate`, and `$afterUpdate` to method names on the model. Hooks run around inserts and updates when `$allowCallbacks` is enabled.
