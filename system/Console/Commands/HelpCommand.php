<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Kernel;
use WTD\Console\Output;

/**
 * Displays console command help.
 */
final class HelpCommand implements Command
{
    public function __construct(private readonly Kernel $kernel)
    {
    }

    public function name(): string
    {
        return 'help';
    }

    public function description(): string
    {
        return 'Show command help.';
    }

    public function handle(Input $input, Output $output): int
    {
        $commandName = $input->argument(0);

        if ($commandName === null) {
            $output->line('Usage: php core <command> [arguments] [--option=value]');
            $output->line();
            $output->line('Available commands:');

            foreach ($this->kernel->commands() as $command) {
                $output->line(sprintf('%-15s %s', $command->name(), $command->description()));
            }

            return 0;
        }

        $commands = $this->kernel->commands();

        if (!array_key_exists($commandName, $commands)) {
            $output->error(sprintf('Unknown command: %s', $commandName));

            return 1;
        }

        $command = $commands[$commandName];
        $output->line($command->name());
        $output->line($command->description());

        return 0;
    }
}
