<?php

declare(strict_types=1);

namespace WTD\ORM\Relations;

use WTD\ORM\Model;

/**
 * Polymorphic one-to-many relationship.
 *
 * @template TRelated of Model
 *
 * @extends Relation<TRelated>
 */
final class MorphMany extends Relation
{
    /**
     * @return list<TRelated>
     */
    public function getResults(): array
    {
        return $this->get();
    }
}
