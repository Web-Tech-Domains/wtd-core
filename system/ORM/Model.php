<?php

declare(strict_types=1);

namespace WTD\ORM;

use RuntimeException;
use WTD\Database\Connection;
use WTD\ORM\Relations\BelongsTo;
use WTD\ORM\Relations\BelongsToMany;
use WTD\ORM\Relations\HasMany;
use WTD\ORM\Relations\HasOne;
use WTD\ORM\Relations\MorphMany;
use WTD\ORM\Relations\MorphTo;

/**
 * Minimal active-record model foundation backed by the database query builder.
 *
 * @phpstan-consistent-constructor
 */
abstract class Model
{
    protected static ?Connection $connection = null;

    /**
     * @var array<class-string<Model>, array<string, list<callable(Model): void>>>
     */
    private static array $listeners = [];

    /**
     * @var array<class-string<Model>, list<object>>
     */
    private static array $observers = [];

    protected string $primaryKey = 'id';

    protected ?string $table = null;

    protected bool $softDeletes = false;

    protected bool $usesUuids = false;

    /**
     * @var array<string, string>
     */
    protected array $casts = [];

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
     * Register a model event listener.
     *
     * @param callable(Model): void $listener
     */
    public static function registerModelEvent(string $event, callable $listener): void
    {
        self::$listeners[static::class][$event][] = $listener;
    }

    /**
     * Register an observer object with methods matching model event names.
     */
    public static function observe(object $observer): void
    {
        self::$observers[static::class][] = $observer;
    }

    /**
     * Clear registered listeners and observers for this model class.
     */
    public static function flushModelEvents(): void
    {
        unset(self::$listeners[static::class], self::$observers[static::class]);
    }

    /**
     * @return ModelQueryBuilder<static>
     */
    public static function query(): ModelQueryBuilder
    {
        $model = new static();

        $query = new ModelQueryBuilder(static::class, static::connection()->table($model->tableName()));

        if ($model->usesSoftDeletes()) {
            $query->whereNull($model->deletedAtColumn());
        }

        return $query;
    }

    /**
     * @return ModelQueryBuilder<static>
     */
    public static function withTrashed(): ModelQueryBuilder
    {
        $model = new static();

        return new ModelQueryBuilder(static::class, static::connection()->table($model->tableName()));
    }

    /**
     * @return ModelQueryBuilder<static>
     */
    public static function onlyTrashed(): ModelQueryBuilder
    {
        $model = new static();

        return static::withTrashed()->whereNotNull($model->deletedAtColumn());
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

    public static function getConnection(): Connection
    {
        return static::connection();
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
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        $value = $this->attributes[$key] ?? null;
        $accessor = 'get' . $this->studly($key) . 'Attribute';

        if (method_exists($this, $accessor)) {
            return $this->{$accessor}($value);
        }

        return $this->castAttribute($key, $value);
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $mutator = 'set' . $this->studly($key) . 'Attribute';

        if (method_exists($this, $mutator)) {
            $value = $this->{$mutator}($value);
        }

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

    public function getMorphClass(): string
    {
        return static::class;
    }

    public function save(): bool
    {
        $this->fireModelEvent('saving');

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

        $this->fireModelEvent('deleting');

        if ($this->usesSoftDeletes()) {
            $this->attributes[$this->deletedAtColumn()] = date('Y-m-d H:i:s');
            $affected = $this->performUpdate();

            if ($affected) {
                $this->fireModelEvent('deleted');
            }

            return $affected;
        }

        $affected = static::connection()
            ->table($this->tableName())
            ->where($this->primaryKey, $this->attributes[$this->primaryKey] ?? null)
            ->delete();

        if ($affected > 0) {
            $this->exists = false;
            $this->fireModelEvent('deleted');
        }

        return $affected > 0;
    }

    public function restore(): bool
    {
        if (!$this->exists || !$this->usesSoftDeletes()) {
            return false;
        }

        $this->fireModelEvent('restoring');
        $this->attributes[$this->deletedAtColumn()] = null;
        $restored = $this->performUpdate();

        if ($restored) {
            $this->fireModelEvent('restored');
        }

        return $restored;
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

    protected function usesSoftDeletes(): bool
    {
        return $this->softDeletes;
    }

    protected function deletedAtColumn(): string
    {
        return 'deleted_at';
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

    /**
     * @template TRelated of Model
     *
     * @param class-string<TRelated> $related
     *
     * @return BelongsToMany<TRelated>
     */
    protected function belongsToMany(
        string $related,
        string $pivotTable,
        string $foreignPivotKey,
        string $relatedPivotKey,
        ?string $parentKey = null,
        ?string $relatedKey = null,
    ): BelongsToMany {
        return new BelongsToMany(
            $this,
            $related,
            $pivotTable,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?? $this->primaryKey,
            $relatedKey ?? (new $related())->getKeyName(),
        );
    }

    /**
     * @template TRelated of Model
     *
     * @param class-string<TRelated> $related
     *
     * @return MorphMany<TRelated>
     */
    protected function morphMany(string $related, string $name): MorphMany
    {
        /** @var MorphMany<TRelated> $relation */
        $relation = new MorphMany(
            $related::query()
                ->where($name . '_type', $this->getMorphClass())
                ->where($name . '_id', $this->getKey()),
        );

        return $relation;
    }

    /**
     * @param array<string, class-string<Model>> $morphMap
     */
    protected function morphTo(string $name, array $morphMap = []): MorphTo
    {
        return new MorphTo(
            is_string($this->attributes[$name . '_type'] ?? null) ? $this->attributes[$name . '_type'] : null,
            $this->attributes[$name . '_id'] ?? null,
            $morphMap,
        );
    }

    private function performInsert(): bool
    {
        $this->fireModelEvent('creating');

        if ($this->usesUuids && !array_key_exists($this->primaryKey, $this->attributes)) {
            $this->attributes[$this->primaryKey] = $this->newUuid();
        }

        $affected = static::connection()->table($this->tableName())->insert($this->attributes);

        if ($affected > 0) {
            if (!array_key_exists($this->primaryKey, $this->attributes)) {
                $this->attributes[$this->primaryKey] = (int) static::connection()->pdo()->lastInsertId();
            }

            $this->exists = true;
            $this->original = $this->attributes;
            $this->fireModelEvent('created');
            $this->fireModelEvent('saved');
        }

        return $affected > 0;
    }

    private function newUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20),
        );
    }

    private function performUpdate(): bool
    {
        $this->fireModelEvent('updating');
        $key = $this->attributes[$this->primaryKey] ?? null;
        $values = $this->attributes;
        unset($values[$this->primaryKey]);

        $affected = static::connection()
            ->table($this->tableName())
            ->where($this->primaryKey, $key)
            ->update($values);

        if ($affected > 0) {
            $this->original = $this->attributes;
            $this->fireModelEvent('updated');
            $this->fireModelEvent('saved');
        }

        return $affected > 0;
    }

    private function castAttribute(string $key, mixed $value): mixed
    {
        if ($value === null || !array_key_exists($key, $this->casts)) {
            return $value;
        }

        return match ($this->casts[$key]) {
            'int', 'integer' => (int) $value,
            'float', 'double', 'real' => (float) $value,
            'bool', 'boolean' => (bool) $value,
            'string' => (string) $value,
            'array', 'json' => is_array($value) ? $value : $this->decodeJsonArray((string) $value),
            default => $value,
        };
    }

    /**
     * @return array<mixed>
     */
    private function decodeJsonArray(string $value): array
    {
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function fireModelEvent(string $event): void
    {
        foreach (self::$listeners[static::class][$event] ?? [] as $listener) {
            $listener($this);
        }

        foreach (self::$observers[static::class] ?? [] as $observer) {
            if (method_exists($observer, $event)) {
                $observer->{$event}($this);
            }
        }
    }

    private function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}
