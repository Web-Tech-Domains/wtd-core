<?php

declare(strict_types=1);

namespace WTD\Console;

/**
 * Represents CLI input arguments.
 */
final class Input
{
    /**
     * @var list<string>
     */
    private array $positionals;

    /**
     * @var array<string, string|bool>
     */
    private array $options;

    /**
     * @param list<string> $arguments
     */
    public function __construct(private readonly array $arguments)
    {
        [$this->positionals, $this->options] = $this->parse(array_slice($arguments, 1));
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
        return $this->positionals;
    }

    /**
     * Return a positional argument by zero-based index.
     */
    public function argument(int $index, ?string $default = null): ?string
    {
        return $this->positionals[$index] ?? $default;
    }

    /**
     * Determine whether an option was provided.
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Return an option value.
     */
    public function option(string $name, string|bool|null $default = null): string|bool|null
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * Return parsed options.
     *
     * @return array<string, string|bool>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Parse positional arguments and long options.
     *
     * @param list<string> $tokens
     *
     * @return array{0: list<string>, 1: array<string, string|bool>}
     */
    private function parse(array $tokens): array
    {
        $positionals = [];
        $options = [];

        foreach ($tokens as $token) {
            if (!str_starts_with($token, '--')) {
                $positionals[] = $token;
                continue;
            }

            $option = substr($token, 2);

            if ($option === '') {
                continue;
            }

            if (str_contains($option, '=')) {
                [$name, $value] = explode('=', $option, 2);
                $options[$name] = $value;
                continue;
            }

            $options[$option] = true;
        }

        return [$positionals, $options];
    }
}
