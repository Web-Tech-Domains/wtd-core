<?php

declare(strict_types=1);

namespace WTD\ORM;

use BadMethodCallException;
use WTD\Database\QueryBuilder;

/**
 * Maps database query results into ORM model instances.
 *
 * @template TModel of Model
 */
final class ModelQueryBuilder
{
    /**
     * @param class-string<TModel> $modelClass
     */
    public function __construct(
        private readonly string $modelClass,
        private readonly QueryBuilder $query,
    ) {
    }

    /**
     * @return self<TModel>
     */
    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $this->query->where($column, $operatorOrValue, $value);

        return $this;
    }

    /**
     * @return self<TModel>
     */
    public function whereNull(string $column): self
    {
        $this->query->whereNull($column);

        return $this;
    }

    /**
     * @return self<TModel>
     */
    public function whereNotNull(string $column): self
    {
        $this->query->whereNotNull($column);

        return $this;
    }

    /**
     * @return self<TModel>
     */
    public function limit(int $limit): self
    {
        $this->query->limit($limit);

        return $this;
    }

    /**
     * @return self<TModel>
     */
    public function offset(int $offset): self
    {
        $this->query->offset($offset);

        return $this;
    }

    /**
     * @return list<TModel>
     */
    public function get(): array
    {
        return array_map(
            fn (array $row): Model => $this->model($row),
            $this->query->get(),
        );
    }

    /**
     * @return TModel|null
     */
    public function first(): ?Model
    {
        $row = $this->query->first();

        return $row === null ? null : $this->model($row);
    }

    public function count(): int
    {
        return $this->query->count();
    }

    /**
     * Call a local scope defined on the model as scopeName(ModelQueryBuilder $query, ...$args).
     *
     * @param list<mixed> $arguments
     *
     * @return self<TModel>
     */
    public function __call(string $method, array $arguments): self
    {
        $scope = 'scope' . ucfirst($method);

        if (!method_exists($this->modelClass, $scope)) {
            throw new BadMethodCallException(sprintf('Scope [%s] is not defined on model [%s].', $method, $this->modelClass));
        }

        $model = new $this->modelClass();
        $result = $model->{$scope}($this, ...$arguments);

        return $result instanceof self ? $result : $this;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return TModel
     */
    private function model(array $row): Model
    {
        return $this->modelClass::fromDatabase($row);
    }
}
