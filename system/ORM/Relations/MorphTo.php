<?php

declare(strict_types=1);

namespace WTD\ORM\Relations;

use WTD\ORM\Model;

/**
 * Polymorphic inverse relationship.
 */
final class MorphTo
{
    /**
     * @param array<string, class-string<Model>> $morphMap
     */
    public function __construct(
        private readonly ?string $type,
        private readonly mixed $id,
        private readonly array $morphMap = [],
    ) {
    }

    public function getResult(): ?Model
    {
        if ($this->type === null) {
            return null;
        }

        $class = $this->morphMap[$this->type] ?? $this->type;

        if (!is_a($class, Model::class, true)) {
            return null;
        }

        return $class::find($this->id);
    }
}
