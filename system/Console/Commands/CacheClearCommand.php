<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Cache\CacheManager;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

final class CacheClearCommand implements Command
{
    public function name(): string
    {
        return 'cache:clear';
    }

    public function description(): string
    {
        return 'Clear the application cache store.';
    }

    public function handle(Input $input, Output $output): int
    {
        $store = is_string($input->option('store')) ? (string) $input->option('store') : 'file';
        (new CacheManager($store))->store($store)->flush();
        $output->line('Application cache cleared');

        return 0;
    }
}
