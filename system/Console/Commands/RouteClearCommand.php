<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Routing\RouteCache;

/**
 * Clears the route cache.
 */
final class RouteClearCommand implements Command
{
    public function __construct(private readonly RouteCache $cache)
    {
    }

    public function name(): string
    {
        return 'route:clear';
    }

    public function description(): string
    {
        return 'Clear the route cache.';
    }

    public function handle(Input $input, Output $output): int
    {
        $this->cache->clear();
        $output->line('Route cache cleared');

        return 0;
    }
}
