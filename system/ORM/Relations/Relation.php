<?php

declare(strict_types=1);

namespace WTD\ORM\Relations;

use WTD\ORM\Model;
use WTD\ORM\ModelQueryBuilder;

/**
 * Base class for ORM relationships.
 *
 * @template TRelated of Model
 */
abstract class Relation
{
    /**
     * @param ModelQueryBuilder<TRelated> $query
     */
    public function __construct(protected readonly ModelQueryBuilder $query)
    {
    }

    /**
     * @return list<TRelated>
     */
    public function get(): array
    {
        return $this->query->get();
    }

    /**
     * @return TRelated|null
     */
    public function first(): ?Model
    {
        return $this->query->first();
    }
}
