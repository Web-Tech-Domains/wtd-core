<?php

declare(strict_types=1);

namespace WTD\ORM\Relations;

use WTD\ORM\Model;

/**
 * One-to-one child relationship.
 *
 * @template TRelated of Model
 *
 * @extends Relation<TRelated>
 */
final class HasOne extends Relation
{
    /**
     * @return TRelated|null
     */
    public function getResult(): ?Model
    {
        return $this->first();
    }
}
