<?php

declare(strict_types=1);

namespace WTD\Console;

/**
 * Defines a command executable by the WTD console kernel.
 */
interface Command
{
    /**
     * Return the command name used on the CLI.
     */
    public function name(): string;

    /**
     * Return a short command description.
     */
    public function description(): string;

    /**
     * Execute the command.
     */
    public function handle(Input $input, Output $output): int;
}
