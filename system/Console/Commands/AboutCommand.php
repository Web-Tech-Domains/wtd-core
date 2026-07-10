<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

/**
 * Prints framework identity.
 */
final class AboutCommand implements Command
{
    public function __construct(private readonly Application $app)
    {
    }

    public function name(): string
    {
        return 'about';
    }

    public function description(): string
    {
        return 'Print framework name and version.';
    }

    public function handle(Input $input, Output $output): int
    {
        $output->line($this->app->name() . ' ' . $this->app->version());

        return 0;
    }
}
