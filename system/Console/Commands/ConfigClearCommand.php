<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Config\Cache;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

/**
 * Clears the framework configuration cache file.
 */
final class ConfigClearCommand implements Command
{
    public function __construct(private readonly Cache $cache)
    {
    }

    public function name(): string
    {
        return 'config:clear';
    }

    public function description(): string
    {
        return 'Clear the configuration cache.';
    }

    public function handle(Input $input, Output $output): int
    {
        $this->cache->clear();
        $output->line('Configuration cache cleared');

        return 0;
    }
}
