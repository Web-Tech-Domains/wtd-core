<?php

declare(strict_types=1);

namespace WTD\Console;

/**
 * Represents CLI input arguments.
 */
final class Input
{
    /**
     * @param list<string> $arguments
     */
    public function __construct(private readonly array $arguments)
    {
    }

    /**
     * Create input from the PHP argv array.
     *
     * @param list<string> $argv
     */
    public static function fromArgv(array $argv): self
    {
        return new self(array_slice($argv, 1));
    }

    /**
     * Return the command name, falling back to the default command.
     */
    public function commandName(string $default = 'about'): string
    {
        return $this->arguments[0] ?? $default;
    }

    /**
     * Return all command arguments after the command name.
     *
     * @return list<string>
     */
    public function arguments(): array
    {
        return array_slice($this->arguments, 1);
    }
}
