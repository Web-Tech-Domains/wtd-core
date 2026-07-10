<?php

declare(strict_types=1);

namespace WTD\Exception;

use RuntimeException;

/**
 * Represents an HTTP-aware exception.
 */
class HttpException extends RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
    ) {
        parent::__construct($message === '' ? 'HTTP Error' : $message, $statusCode);
    }

    /**
     * Return the HTTP response status code.
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
