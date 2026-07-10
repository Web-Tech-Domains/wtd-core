<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

/**
 * Prints the configured application environment.
 */
final class EnvironmentCommand implements Command
{
    public function __construct(private readonly Application $app)
    {
    }

    public function name(): string
    {
        return 'env';
    }

    public function description(): string
    {
        return 'Print current application environment.';
    }

    public function handle(Input $input, Output $output): int
    {
        $output->line((string) $this->app->config()->get('app.env', 'production'));

        return 0;
    }
}
