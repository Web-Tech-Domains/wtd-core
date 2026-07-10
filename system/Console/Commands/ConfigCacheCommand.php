<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Config\Cache;
use WTD\Config\Loader;
use WTD\Config\Repository;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

/**
 * Builds the framework configuration cache file.
 */
final class ConfigCacheCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Cache $cache,
    ) {
    }

    public function name(): string
    {
        return 'config:cache';
    }

    public function description(): string
    {
        return 'Build the configuration cache.';
    }

    public function handle(Input $input, Output $output): int
    {
        $repository = new Repository();
        (new Loader($repository))->loadDirectory($this->app->basePath('config'));
        $this->cache->write($repository->all());

        $output->line('Configuration cached');

        return 0;
    }
}
