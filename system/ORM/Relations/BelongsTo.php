<?php

declare(strict_types=1);

namespace WTD\ORM\Relations;

use WTD\ORM\Model;

/**
 * Inverse one-to-one relationship.
 *
 * @template TRelated of Model
 *
 * @extends Relation<TRelated>
 */
final class BelongsTo extends Relation
{
    /**
     * @return TRelated|null
     */
    public function getResult(): ?Model
    {
        return $this->first();
    }
}
