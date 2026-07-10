<?php

declare(strict_types=1);

namespace WTD\ORM\Relations;

use WTD\Database\Connection;
use WTD\ORM\Model;

/**
 * Many-to-many relationship through a pivot table.
 *
 * @template TRelated of Model
 */
final class BelongsToMany
{
    /**
     * @param class-string<TRelated> $related
     */
    public function __construct(
        private readonly Model $parent,
        private readonly string $related,
        private readonly string $pivotTable,
        private readonly string $foreignPivotKey,
        private readonly string $relatedPivotKey,
        private readonly string $parentKey,
        private readonly string $relatedKey,
    ) {
    }

    /**
     * @return list<TRelated>
     */
    public function getResults(): array
    {
        $rows = $this->connection()->select(
            sprintf(
                'SELECT "%s" FROM "%s" WHERE "%s" = ?',
                $this->relatedPivotKey,
                $this->pivotTable,
                $this->foreignPivotKey,
            ),
            [$this->parent->getAttribute($this->parentKey)],
        );
        $related = [];

        foreach ($rows as $row) {
            $model = $this->relatedModel($row[$this->relatedPivotKey] ?? null);

            if ($model !== null) {
                $related[] = $model;
            }
        }

        return $related;
    }

    public function attach(mixed $id): bool
    {
        return $this->connection()->table($this->pivotTable)->insert([
            $this->foreignPivotKey => $this->parent->getAttribute($this->parentKey),
            $this->relatedPivotKey => $id,
        ]) > 0;
    }

    public function detach(mixed $id): bool
    {
        return $this->connection()
            ->table($this->pivotTable)
            ->where($this->foreignPivotKey, $this->parent->getAttribute($this->parentKey))
            ->where($this->relatedPivotKey, $id)
            ->delete() > 0;
    }

    private function connection(): Connection
    {
        return $this->related::getConnection();
    }

    /**
     * @return TRelated|null
     */
    private function relatedModel(mixed $id): ?Model
    {
        $model = new $this->related();

        if ($this->relatedKey === $model->getKeyName()) {
            return $this->related::find($id);
        }

        return $this->related::query()->where($this->relatedKey, $id)->first();
    }
}
