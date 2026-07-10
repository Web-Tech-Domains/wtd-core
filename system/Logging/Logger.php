<?php

declare(strict_types=1);

namespace WTD\Logging;

use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Writes framework log entries to a local file.
 */
final class Logger implements LoggerInterface
{
    public function __construct(private readonly string $path)
    {
    }

    /**
     * Write an info-level message.
     *
     * @param array<string, mixed> $context
     */
    public function info(Stringable|string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * System is unusable.
     *
     * @param array<string, mixed> $context
     */
    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * @param array<string, mixed> $context
     */
    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Critical condition.
     *
     * @param array<string, mixed> $context
     */
    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Write an error-level message.
     *
     * @param array<string, mixed> $context
     */
    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Exceptional occurrence that is not an error.
     *
     * @param array<string, mixed> $context
     */
    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Normal but significant event.
     *
     * @param array<string, mixed> $context
     */
    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Interesting event.
     *
     * @param array<string, mixed> $context
     */
    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Write a log entry.
     *
     * @param array<string, mixed> $context
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        $directory = dirname($this->path);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $entry = sprintf(
            "[%s] %s: %s%s\n",
            gmdate('Y-m-d\TH:i:s\Z'),
            strtoupper((string) $level),
            $this->interpolate((string) $message, $context),
            $context === [] ? '' : ' ' . json_encode($context, JSON_THROW_ON_ERROR),
        );

        file_put_contents($this->path, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Replace PSR-3 placeholders in the message.
     *
     * @param array<string, mixed> $context
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];

        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value === null || $value instanceof Stringable) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }

        return strtr($message, $replace);
    }
}
