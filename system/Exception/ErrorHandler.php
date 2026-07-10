<?php

declare(strict_types=1);

namespace WTD\Exception;

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
        set_exception_handler($this->handle(...));
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
}
