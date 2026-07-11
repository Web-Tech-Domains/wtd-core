<?php

declare(strict_types=1);

namespace WTD\Cache;

final class CacheEvent
{
    public function __construct(
        public readonly string $type,
        public readonly string $key,
    ) {
    }
}
