<?php

declare(strict_types=1);

namespace WTD\Database;

/**
 * Builds and executes simple parameterized SQL queries.
 */
final class QueryBuilder
{
    /**
     * @var list<string>
     */
    private array $columns = ['*'];

    /**
     * @var list<string>
     */
    private array $wheres = [];

    /**
     * @var list<mixed>
     */
    private array $bindings = [];

    private ?int $limit = null;

    private ?int $offset = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
    ) {
    }

    /**
     * Select columns for the query.
     *
     * @param non-empty-string ...$columns
     */
    public function select(string ...$columns): self
    {
        if ($columns !== []) {
            $this->columns = array_values($columns);
        }

        return $this;
    }

    /**
     * Add a where condition.
     */
    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $operator = $value === null ? '=' : (string) $operatorOrValue;
        $binding = $value === null ? $operatorOrValue : $value;

        $this->wheres[] = sprintf('%s %s ?', $this->quote($column), $operator);
        $this->bindings[] = $binding;

        return $this;
    }

    /**
     * Limit the number of rows returned.
     */
    public function limit(int $limit): self
    {
        $this->limit = max(0, $limit);

        return $this;
    }

    /**
     * Offset the result set.
     */
    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);

        return $this;
    }

    /**
     * Execute the select query.
     *
     * @return list<array<string, mixed>>
     */
    public function get(): array
    {
        return $this->connection->select($this->toSql(), $this->bindings);
    }

    /**
     * Return the first selected row.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $query = clone $this;
        $rows = $query->limit(1)->get();

        return $rows[0] ?? null;
    }

    /**
     * Count matching rows.
     */
    public function count(): int
    {
        $rows = $this->connection->select(sprintf(
            'SELECT COUNT(*) AS aggregate FROM %s%s',
            $this->quote($this->table),
            $this->whereSql(),
        ), $this->bindings);

        return (int) ($rows[0]['aggregate'] ?? 0);
    }

    /**
     * Paginate matching rows.
     */
    public function paginate(int $perPage = 15, int $page = 1): Paginator
    {
        $perPage = max(1, $perPage);
        $page = max(1, $page);
        $query = clone $this;
        $items = $query->limit($perPage)->offset(($page - 1) * $perPage)->get();

        return new Paginator($items, $this->count(), $perPage, $page);
    }

    /**
     * Iterate through matching rows in chunks.
     *
     * @param callable(list<array<string, mixed>>, int): (bool|void) $callback
     */
    public function chunk(int $size, callable $callback): void
    {
        $size = max(1, $size);
        $page = 1;

        do {
            $query = clone $this;
            $rows = $query->limit($size)->offset(($page - 1) * $size)->get();

            if ($rows === []) {
                return;
            }

            if ($callback($rows, $page) === false) {
                return;
            }

            $page++;
        } while (count($rows) === $size);
    }

    /**
     * Insert a row and return affected row count.
     *
     * @param array<string, mixed> $values
     */
    public function insert(array $values): int
    {
        $columns = array_keys($values);
        $placeholders = array_fill(0, count($columns), '?');

        return $this->connection->statement(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->quote($this->table),
            implode(', ', array_map(fn (string $column): string => $this->quote($column), $columns)),
            implode(', ', $placeholders),
        ), array_values($values));
    }

    /**
     * Update matching rows and return affected row count.
     *
     * @param array<string, mixed> $values
     */
    public function update(array $values): int
    {
        $sets = [];

        foreach (array_keys($values) as $column) {
            $sets[] = $this->quote($column) . ' = ?';
        }

        return $this->connection->statement(
            sprintf(
                'UPDATE %s SET %s%s',
                $this->quote($this->table),
                implode(', ', $sets),
                $this->whereSql(),
            ),
            array_merge(array_values($values), $this->bindings),
        );
    }

    /**
     * Delete matching rows and return affected row count.
     */
    public function delete(): int
    {
        return $this->connection->statement(
            sprintf('DELETE FROM %s%s', $this->quote($this->table), $this->whereSql()),
            $this->bindings,
        );
    }

    /**
     * Compile the select SQL.
     */
    public function toSql(): string
    {
        $sql = sprintf(
            'SELECT %s FROM %s%s',
            implode(', ', array_map(fn (string $column): string => $column === '*' ? '*' : $this->quote($column), $this->columns)),
            $this->quote($this->table),
            $this->whereSql(),
        );

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    private function whereSql(): string
    {
        return $this->wheres === [] ? '' : ' WHERE ' . implode(' AND ', $this->wheres);
    }

    private function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
