<?php

declare(strict_types=1);

namespace WTD\ORM;

/**
 * Small repository wrapper for model persistence and lookup.
 *
 * @template TModel of Model
 */
final class ModelRepository
{
    /**
     * @param class-string<TModel> $modelClass
     */
    public function __construct(private readonly string $modelClass)
    {
    }

    /**
     * @return list<TModel>
     */
    public function all(): array
    {
        return $this->modelClass::all();
    }

    /**
     * @return TModel|null
     */
    public function find(mixed $id): ?Model
    {
        return $this->modelClass::find($id);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        $model = new $this->modelClass($attributes);
        $model->save();

        return $model;
    }

    public function save(Model $model): bool
    {
        return $model->save();
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }
}
