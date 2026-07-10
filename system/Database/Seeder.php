<?php

declare(strict_types=1);

namespace WTD\Database;

/**
 * Defines a database seeder.
 */
interface Seeder
{
    /**
     * Seed database records.
     */
    public function run(Connection $connection): void;
}
