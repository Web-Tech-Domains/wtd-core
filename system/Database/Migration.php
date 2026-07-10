<?php

declare(strict_types=1);

namespace WTD\Database;

/**
 * Defines a database migration.
 */
interface Migration
{
    /**
     * Apply the migration.
     */
    public function up(Schema $schema): void;

    /**
     * Reverse the migration.
     */
    public function down(Schema $schema): void;
}
