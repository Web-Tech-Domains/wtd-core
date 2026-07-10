<?php

declare(strict_types=1);

namespace WTD\Exception;

use RuntimeException;

/**
 * Represents an HTTP-aware exception.
 */
class HttpException extends RuntimeException
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
        private readonly array $headers = [],
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

    /**
     * Return headers that should be added to the rendered response.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
