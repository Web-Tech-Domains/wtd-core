<?php

declare(strict_types=1);

namespace WTD\ORM\Relations;

use WTD\ORM\Model;

/**
 * One-to-many child relationship.
 *
 * @template TRelated of Model
 *
 * @extends Relation<TRelated>
 */
final class HasMany extends Relation
{
    /**
     * @return list<TRelated>
     */
    public function getResults(): array
    {
        return $this->get();
    }
}
