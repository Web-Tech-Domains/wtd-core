<?php

declare(strict_types=1);

namespace WTD\Console;

/**
 * Registers and dispatches CLI commands.
 */
final class Kernel
{
    /**
     * @var array<string, Command>
     */
    private array $commands = [];

    /**
     * Register a command instance.
     */
    public function register(Command $command): void
    {
        $this->commands[$command->name()] = $command;
    }

    /**
     * Dispatch the command requested by input.
     */
    public function handle(Input $input, Output $output): int
    {
        $name = $input->commandName();

        if (!array_key_exists($name, $this->commands)) {
            throw new UnknownCommandException(sprintf('Unknown command: %s', $name));
        }

        return $this->commands[$name]->handle($input, $output);
    }

    /**
     * Return all registered commands.
     *
     * @return array<string, Command>
     */
    public function commands(): array
    {
        ksort($this->commands);

        return $this->commands;
    }
}
