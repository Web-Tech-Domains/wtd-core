<?php

declare(strict_types=1);

namespace WTD\Database;

/**
 * Builds a table definition for schema operations.
 */
final class Blueprint
{
    /**
     * @var list<string>
     */
    private array $columns = [];

    public function __construct(private readonly string $table)
    {
    }

    /**
     * Add an auto-incrementing integer primary key.
     */
    public function id(string $name = 'id'): self
    {
        $this->columns[] = sprintf('%s INTEGER PRIMARY KEY AUTOINCREMENT', $this->quote($name));

        return $this;
    }

    /**
     * Add a string column.
     */
    public function string(string $name, int $length = 255): self
    {
        $this->columns[] = sprintf('%s VARCHAR(%d) NOT NULL', $this->quote($name), $length);

        return $this;
    }

    /**
     * Add a nullable string column.
     */
    public function nullableString(string $name, int $length = 255): self
    {
        $this->columns[] = sprintf('%s VARCHAR(%d) NULL', $this->quote($name), $length);

        return $this;
    }

    /**
     * Add an integer column.
     */
    public function integer(string $name): self
    {
        $this->columns[] = sprintf('%s INTEGER NOT NULL', $this->quote($name));

        return $this;
    }

    /**
     * Add a boolean column.
     */
    public function boolean(string $name): self
    {
        $this->columns[] = sprintf('%s TINYINT(1) NOT NULL', $this->quote($name));

        return $this;
    }

    /**
     * Add a text column.
     */
    public function text(string $name): self
    {
        $this->columns[] = sprintf('%s TEXT NOT NULL', $this->quote($name));

        return $this;
    }

    /**
     * Add created_at and updated_at timestamp columns.
     */
    public function timestamps(): self
    {
        $this->columns[] = sprintf('%s DATETIME NULL', $this->quote('created_at'));
        $this->columns[] = sprintf('%s DATETIME NULL', $this->quote('updated_at'));

        return $this;
    }

    /**
     * Add a nullable deleted_at column for soft deletes.
     */
    public function softDeletes(string $name = 'deleted_at'): self
    {
        $this->columns[] = sprintf('%s DATETIME NULL', $this->quote($name));

        return $this;
    }

    /**
     * Return the table name.
     */
    public function table(): string
    {
        return $this->table;
    }

    /**
     * Compile the blueprint columns.
     *
     * @return list<string>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    private function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
