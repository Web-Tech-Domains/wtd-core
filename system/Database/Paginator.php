<?php

declare(strict_types=1);

namespace WTD\Database;

/**
 * Represents a paginated query result.
 */
final class Paginator
{
    /**
     * @param list<array<string, mixed>> $items
     */
    public function __construct(
        private readonly array $items,
        private readonly int $total,
        private readonly int $perPage,
        private readonly int $currentPage,
    ) {
    }

    /**
     * Return the current page items.
     *
     * @return list<array<string, mixed>>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Return the total number of matching rows.
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Return the number of items per page.
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Return the current page number.
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Return the last page number.
     */
    public function lastPage(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }

    /**
     * Return whether another page exists.
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage();
    }

    /**
     * Return array representation.
     *
     * @return array{items: list<array<string, mixed>>, total: int, per_page: int, current_page: int, last_page: int, has_more_pages: bool}
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage(),
            'has_more_pages' => $this->hasMorePages(),
        ];
    }
}
