<?php

declare(strict_types=1);

namespace WTD\Exception;

use ErrorException;
use Throwable;
use WTD\Logging\Logger;

/**
 * Converts uncaught throwables into logged framework failures.
 */
final class ErrorHandler
{
    public function __construct(private readonly Logger $logger)
    {
    }

    /**
     * Register the handler with PHP.
     */
    public function register(): void
    {
        set_error_handler($this->handleError(...));
        set_exception_handler($this->handle(...));
        register_shutdown_function($this->handleShutdown(...));
    }

    /**
     * Convert PHP errors into exceptions so HTTP and CLI flows handle them consistently.
     *
     * @throws ErrorException
     */
    public function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if ((error_reporting() & $severity) === 0) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Log an uncaught throwable.
     */
    public function handle(Throwable $throwable): void
    {
        $this->logger->error($throwable->getMessage(), [
            'exception' => $throwable::class,
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ]);
    }

    /**
     * Log fatal shutdown errors that PHP cannot route through the normal exception handler.
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if (!is_array($error)) {
            return;
        }

        $type = $error['type'] ?? null;

        if (!is_int($type) || !in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            return;
        }

        $message = is_string($error['message'] ?? null) ? $error['message'] : 'Fatal PHP error';
        $file = is_string($error['file'] ?? null) ? $error['file'] : '';
        $line = is_int($error['line'] ?? null) ? $error['line'] : 0;

        $this->logger->error($message, [
            'exception' => 'FatalError',
            'file' => $file,
            'line' => $line,
        ]);
    }
}
