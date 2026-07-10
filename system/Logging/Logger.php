<?php

declare(strict_types=1);

namespace WTD\Logging;

/**
 * Writes framework log entries to a local file.
 */
final class Logger
{
    public function __construct(private readonly string $path)
    {
    }

    /**
     * Write an info-level message.
     *
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Write an error-level message.
     *
     * @param array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Write a log entry.
     *
     * @param array<string, mixed> $context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $directory = dirname($this->path);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $entry = sprintf(
            "[%s] %s: %s%s\n",
            gmdate('Y-m-d\TH:i:s\Z'),
            strtoupper($level),
            $message,
            $context === [] ? '' : ' ' . json_encode($context, JSON_THROW_ON_ERROR),
        );

        file_put_contents($this->path, $entry, FILE_APPEND | LOCK_EX);
    }
}
