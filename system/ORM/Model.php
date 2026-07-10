<?php

declare(strict_types=1);

namespace WTD\ORM;

use RuntimeException;
use WTD\Database\Connection;
use WTD\ORM\Relations\BelongsTo;
use WTD\ORM\Relations\HasMany;
use WTD\ORM\Relations\HasOne;

/**
 * Minimal active-record model foundation backed by the database query builder.
 *
 * @phpstan-consistent-constructor
 */
abstract class Model
{
    protected static ?Connection $connection = null;

    protected string $primaryKey = 'id';

    protected ?string $table = null;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<string, mixed>
     */
    protected array $original = [];

    protected bool $exists = false;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->fill($attributes);
        $this->exists = $exists;
        $this->original = $this->attributes;
    }

    public static function setConnection(?Connection $connection): void
    {
        static::$connection = $connection;
    }

    /**
     * @return ModelQueryBuilder<static>
     */
    public static function query(): ModelQueryBuilder
    {
        $model = new static();

        return new ModelQueryBuilder(static::class, static::connection()->table($model->tableName()));
    }

    /**
     * @return list<static>
     */
    public static function all(): array
    {
        return static::query()->get();
    }

    public static function find(mixed $id): ?static
    {
        $model = new static();

        return static::query()
            ->where($model->primaryKey, $id)
            ->first();
    }

    /**
     * Hydrate a model from a database row.
     *
     * @param array<string, mixed> $attributes
     */
    public static function fromDatabase(array $attributes): static
    {
        return new static($attributes, true);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getTable(): string
    {
        return $this->tableName();
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $affected = static::connection()
            ->table($this->tableName())
            ->where($this->primaryKey, $this->attributes[$this->primaryKey] ?? null)
            ->delete();

        if ($affected > 0) {
            $this->exists = false;
        }

        return $affected > 0;
    }

    protected static function connection(): Connection
    {
        if (static::$connection === null) {
            throw new RuntimeException('No database connection has been configured for ORM models.');
        }

        return static::$connection;
    }

    protected function tableName(): string
    {
        if ($this->table !== null) {
            return $this->table;
        }

        $class = static::class;
        $base = substr($class, (int) strrpos($class, '\\') + 1);

        return strtolower($base) . 's';
    }

    /**
     * @template TRelated of Model
     *
     * @param class-string<TRelated> $related
     *
     * @return HasOne<TRelated>
     */
    protected function hasOne(string $related, string $foreignKey, ?string $localKey = null): HasOne
    {
        /** @var HasOne<TRelated> $relation */
        $relation = new HasOne($related::query()->where($foreignKey, $this->attributes[$localKey ?? $this->primaryKey] ?? null));

        return $relation;
    }

    /**
     * @template TRelated of Model
     *
     * @param class-string<TRelated> $related
     *
     * @return HasMany<TRelated>
     */
    protected function hasMany(string $related, string $foreignKey, ?string $localKey = null): HasMany
    {
        /** @var HasMany<TRelated> $relation */
        $relation = new HasMany($related::query()->where($foreignKey, $this->attributes[$localKey ?? $this->primaryKey] ?? null));

        return $relation;
    }

    /**
     * @template TRelated of Model
     *
     * @param class-string<TRelated> $related
     *
     * @return BelongsTo<TRelated>
     */
    protected function belongsTo(string $related, string $foreignKey, ?string $ownerKey = null): BelongsTo
    {
        $model = new $related();

        /** @var BelongsTo<TRelated> $relation */
        $relation = new BelongsTo($related::query()->where($ownerKey ?? $model->getKeyName(), $this->attributes[$foreignKey] ?? null));

        return $relation;
    }

    private function performInsert(): bool
    {
        $affected = static::connection()->table($this->tableName())->insert($this->attributes);

        if ($affected > 0) {
            if (!array_key_exists($this->primaryKey, $this->attributes)) {
                $this->attributes[$this->primaryKey] = (int) static::connection()->pdo()->lastInsertId();
            }

            $this->exists = true;
            $this->original = $this->attributes;
        }

        return $affected > 0;
    }

    private function performUpdate(): bool
    {
        $key = $this->attributes[$this->primaryKey] ?? null;
        $values = $this->attributes;
        unset($values[$this->primaryKey]);

        $affected = static::connection()
            ->table($this->tableName())
            ->where($this->primaryKey, $key)
            ->update($values);

        if ($affected > 0) {
            $this->original = $this->attributes;
        }

        return $affected > 0;
    }
}
