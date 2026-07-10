<?php

declare(strict_types=1);

namespace WTD\Database;

use InvalidArgumentException;

/**
 * Base class for database factories that generate and persist table records.
 */
abstract class Factory
{
    private int $count = 1;

    /**
     * Return the table name this factory writes to.
     */
    abstract protected function table(): string;

    /**
     * Return one generated record.
     *
     * @return array<string, mixed>
     */
    abstract protected function definition(int $sequence): array;

    /**
     * Clone this factory with a custom generation count.
     */
    public function count(int $count): static
    {
        if ($count < 1) {
            throw new InvalidArgumentException('Factory count must be at least 1.');
        }

        $factory = clone $this;
        $factory->count = $count;

        return $factory;
    }

    /**
     * Generate records without persisting them.
     *
     * @return list<array<string, mixed>>
     */
    public function make(): array
    {
        $records = [];

        for ($sequence = 1; $sequence <= $this->count; $sequence++) {
            $records[] = $this->definition($sequence);
        }

        return $records;
    }

    /**
     * Generate and insert records.
     *
     * @return list<array<string, mixed>>
     */
    public function create(Connection $connection): array
    {
        $records = $this->make();

        foreach ($records as $record) {
            $connection->table($this->table())->insert($record);
        }

        return $records;
    }
}
