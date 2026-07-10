<?php

declare(strict_types=1);

namespace WTD\Database;

use Closure;
use PDO;
use PDOStatement;
use Throwable;

/**
 * Thin PDO connection wrapper with common query helpers.
 */
final class Connection
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Return the underlying PDO instance.
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Start a query builder for a table.
     */
    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    /**
     * Run a select query and return associative rows.
     *
     * @param array<int|string, mixed> $bindings
     *
     * @return list<array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        $statement = $this->run($sql, $bindings);

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * Run a statement and return affected rows.
     *
     * @param array<int|string, mixed> $bindings
     */
    public function statement(string $sql, array $bindings = []): int
    {
        return $this->run($sql, $bindings)->rowCount();
    }

    /**
     * Run a callback inside a transaction.
     *
     * @template T
     *
     * @param Closure(self): T $callback
     *
     * @return T
     *
     * @throws Throwable
     */
    public function transaction(Closure $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback($this);
            $this->pdo->commit();

            return $result;
        } catch (Throwable $throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $throwable;
        }
    }

    /**
     * @param array<int|string, mixed> $bindings
     */
    private function run(string $sql, array $bindings): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);

        foreach ($bindings as $key => $value) {
            $parameter = is_int($key) ? $key + 1 : ':' . ltrim((string) $key, ':');
            $statement->bindValue($parameter, $value);
        }

        $statement->execute();

        return $statement;
    }
}
