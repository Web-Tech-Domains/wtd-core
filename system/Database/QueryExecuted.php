<?php

declare(strict_types=1);

namespace WTD\Database;

/**
 * Describes a database query after execution.
 */
final class QueryExecuted
{
    /**
     * @param array<int|string, mixed> $bindings
     */
    public function __construct(
        public readonly string $sql,
        public readonly array $bindings,
        public readonly float $timeMs,
        public readonly Connection $connection,
    ) {
    }
}
