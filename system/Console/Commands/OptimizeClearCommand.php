<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Config\Cache;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Routing\RouteCache;

/**
 * Clears framework optimization caches.
 */
final class OptimizeClearCommand implements Command
{
    public function __construct(
        private readonly Cache $configCache,
        private readonly RouteCache $routeCache,
    ) {
    }

    public function name(): string
    {
        return 'optimize:clear';
    }

    public function description(): string
    {
        return 'Clear framework optimization caches.';
    }

    public function handle(Input $input, Output $output): int
    {
        $this->configCache->clear();
        $this->routeCache->clear();
        $output->line('Framework optimization cache cleared');

        return 0;
    }
}
