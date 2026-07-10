<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Kernel;
use WTD\Console\Output;

/**
 * Lists registered console commands.
 */
final class ListCommand implements Command
{
    public function __construct(private readonly Kernel $kernel)
    {
    }

    public function name(): string
    {
        return 'list';
    }

    public function description(): string
    {
        return 'List available commands.';
    }

    public function handle(Input $input, Output $output): int
    {
        foreach ($this->kernel->commands() as $command) {
            $output->line(sprintf('%-15s %s', $command->name(), $command->description()));
        }

        return 0;
    }
}
