<?php

declare(strict_types=1);

namespace WTD\Database;

/**
 * Compiles query builder state into SQL.
 */
final class QueryGrammar
{
    public function __construct(
        private readonly string $identifierQuote = '"',
    ) {
    }

    public static function forDriver(string $driver): self
    {
        return match (strtolower($driver)) {
            'mysql', 'mariadb' => new self('`'),
            default => new self(),
        };
    }

    /**
     * @param list<string> $columns
     * @param list<string> $wheres
     * @param list<array{column: string, direction: 'ASC'|'DESC'}> $orders
     */
    public function compileSelect(
        string $table,
        array $columns,
        array $wheres,
        array $orders,
        ?int $limit,
        ?int $offset,
    ): string {
        $sql = sprintf(
            'SELECT %s FROM %s%s',
            implode(', ', array_map(fn (string $column): string => $this->column($column), $columns)),
            $this->wrap($table),
            $this->compileWheres($wheres),
        );

        if ($orders !== []) {
            $sql .= ' ORDER BY ' . implode(', ', array_map(
                fn (array $order): string => $this->wrap($order['column']) . ' ' . $order['direction'],
                $orders,
            ));
        }

        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }

        if ($offset !== null) {
            $sql .= ' OFFSET ' . $offset;
        }

        return $sql;
    }

    /**
     * @param list<string> $wheres
     */
    public function compileCount(string $table, array $wheres): string
    {
        return sprintf(
            'SELECT COUNT(*) AS aggregate FROM %s%s',
            $this->wrap($table),
            $this->compileWheres($wheres),
        );
    }

    /**
     * @param list<string> $columns
     */
    public function compileInsert(string $table, array $columns): string
    {
        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->wrap($table),
            implode(', ', array_map(fn (string $column): string => $this->wrap($column), $columns)),
            implode(', ', array_fill(0, count($columns), '?')),
        );
    }

    /**
     * @param list<string> $columns
     * @param list<string> $wheres
     */
    public function compileUpdate(string $table, array $columns, array $wheres): string
    {
        $sets = array_map(fn (string $column): string => $this->wrap($column) . ' = ?', $columns);

        return sprintf(
            'UPDATE %s SET %s%s',
            $this->wrap($table),
            implode(', ', $sets),
            $this->compileWheres($wheres),
        );
    }

    /**
     * @param list<string> $wheres
     */
    public function compileDelete(string $table, array $wheres): string
    {
        return sprintf('DELETE FROM %s%s', $this->wrap($table), $this->compileWheres($wheres));
    }

    public function compileWhere(string $column, string $operator): string
    {
        return sprintf('%s %s ?', $this->wrap($column), $operator);
    }

    public function compileWhereNull(string $column, bool $not = false): string
    {
        return sprintf('%s IS %sNULL', $this->wrap($column), $not ? 'NOT ' : '');
    }

    public function wrap(string $identifier): string
    {
        if ($identifier === '*') {
            return '*';
        }

        return implode('.', array_map(
            fn (string $part): string => $this->identifierQuote . str_replace($this->identifierQuote, $this->identifierQuote . $this->identifierQuote, $part) . $this->identifierQuote,
            explode('.', $identifier),
        ));
    }

    private function column(string $column): string
    {
        return $column === '*' ? '*' : $this->wrap($column);
    }

    /**
     * @param list<string> $wheres
     */
    private function compileWheres(array $wheres): string
    {
        return $wheres === [] ? '' : ' WHERE ' . implode(' AND ', $wheres);
    }
}
