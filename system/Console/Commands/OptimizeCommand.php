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
 * Builds framework optimization caches.
 */
final class OptimizeCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Cache $configCache,
    ) {
    }

    public function name(): string
    {
        return 'optimize';
    }

    public function description(): string
    {
        return 'Build framework optimization caches.';
    }

    public function handle(Input $input, Output $output): int
    {
        $repository = new Repository();
        (new Loader($repository))->loadDirectory($this->app->basePath('config'));
        $this->configCache->write($repository->all());

        $output->line('Framework optimized');

        return 0;
    }
}
