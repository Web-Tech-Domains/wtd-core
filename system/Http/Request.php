<?php

declare(strict_types=1);

namespace WTD\Http;

/**
 * Represents an incoming HTTP request.
 */
final class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, string> $cookies
     * @param array<string, mixed> $server
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $headers = [],
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $cookies = [],
        private readonly array $server = [],
    ) {
    }

    /**
     * Create a request from PHP globals.
     */
    public static function capture(): self
    {
        /** @var array<string, mixed> $server */
        $server = $_SERVER;
        /** @var array<string, mixed> $query */
        $query = $_GET;
        /** @var array<string, mixed> $body */
        $body = $_POST;
        /** @var array<string, string> $cookies */
        $cookies = $_COOKIE;

        return new self(
            strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET')),
            parse_url((string) ($server['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/',
            self::headersFromServer($server),
            $query,
            $body,
            $cookies,
            $server,
        );
    }

    /**
     * Return the HTTP method.
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Return the request path.
     */
    public function path(): string
    {
        return '/' . trim($this->path, '/');
    }

    /**
     * Return a request header value.
     */
    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * Return a query string value.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Return a parsed body value.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Return a cookie value.
     */
    public function cookie(string $key, ?string $default = null): ?string
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Return all server values.
     *
     * @return array<string, mixed>
     */
    public function server(): array
    {
        return $this->server;
    }

    /**
     * @param array<string, mixed> $server
     *
     * @return array<string, string>
     */
    private static function headersFromServer(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $name = strtolower(str_replace('_', '-', substr($key, 5)));
            $headers[$name] = (string) $value;
        }

        return $headers;
    }
}
