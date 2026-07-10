<?php

declare(strict_types=1);

namespace WTD\Console;

/**
 * Writes command output to stdout and stderr streams.
 */
final class Output
{
    /**
     * @param resource $stdout
     * @param resource $stderr
     */
    public function __construct(
        private $stdout,
        private $stderr,
    ) {
    }

    /**
     * Create output using PHP standard streams.
     */
    public static function standard(): self
    {
        return new self(STDOUT, STDERR);
    }

    /**
     * Write a line to stdout.
     */
    public function line(string $message = ''): void
    {
        fwrite($this->stdout, $message . PHP_EOL);
    }

    /**
     * Write a line to stderr.
     */
    public function error(string $message): void
    {
        fwrite($this->stderr, $message . PHP_EOL);
    }

    /**
     * Write a JSON payload to stdout.
     *
     * @param array<string, mixed> $payload
     */
    public function json(array $payload): void
    {
        $this->line(json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
