<?php

declare(strict_types=1);

namespace WTD\Http\Client;

/**
 * Represents an outbound HTTP response.
 */
final class HttpClientResponse
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly int $status,
        array $headers,
        private readonly string $body,
    ) {
        $this->headers = $this->normalizeHeaders($headers);
    }

    /**
     * @var array<string, string>
     */
    private readonly array $headers;

    public function status(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function successful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function failed(): bool
    {
        return $this->status >= 400;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * @return mixed
     */
    public function json(bool $associative = true): mixed
    {
        return json_decode($this->body, $associative, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = $value;
        }

        return $normalized;
    }
}
