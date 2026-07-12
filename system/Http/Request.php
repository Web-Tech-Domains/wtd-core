<?php

declare(strict_types=1);

namespace WTD\Http;

use WTD\Validation\Validator;

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
            self::normalizeCapturedPath($server),
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
     * Return the request host without port.
     */
    public function host(): string
    {
        $host = $this->header('host') ?? (string) ($this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? '');

        return strtolower((string) preg_replace('/:\d+$/', '', $host));
    }

    /**
     * Return the client IP address, honoring X-Forwarded-For only for trusted proxies.
     *
     * @param list<string> $trustedProxies
     */
    public function ip(array $trustedProxies = []): string
    {
        $remote = (string) ($this->server['REMOTE_ADDR'] ?? '');

        if ($trustedProxies === [] && $remote !== '') {
            return $remote;
        }

        if (!in_array('*', $trustedProxies, true) && !in_array($remote, $trustedProxies, true)) {
            return $remote;
        }

        $forwarded = $this->header('x-forwarded-for');

        if ($forwarded === null || trim($forwarded) === '') {
            return $remote;
        }

        return trim(explode(',', $forwarded)[0]);
    }

    /**
     * Return a request header value.
     */
    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * Return all request headers.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Return all query string values.
     *
     * @return array<string, mixed>
     */
    public function queryParams(): array
    {
        return $this->query;
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
     * Return merged query and body input.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    /**
     * Return selected input values.
     *
     * @param list<string> $keys
     *
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        $input = $this->all();
        $selected = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $input)) {
                $selected[$key] = $input[$key];
            }
        }

        return $selected;
    }

    /**
     * Validate merged query and body input.
     *
     * @param array<string, string|list<string>> $rules
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>
     */
    public function validate(Validator $validator, array $rules, array $messages = []): array
    {
        return $validator->validate($this->all(), $rules, $messages);
    }

    /**
     * Return a cookie value.
     */
    public function cookie(string $key, ?string $default = null): ?string
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Return all request cookies.
     *
     * @return array<string, string>
     */
    public function cookies(): array
    {
        return $this->cookies;
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

    /**
     * Normalize the captured URI to the route path.
     *
     * @param array<string, mixed> $server
     */
    private static function normalizeCapturedPath(array $server): string
    {
        $path = parse_url((string) ($server['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
        $scriptDirectory = str_replace('\\', '/', dirname((string) ($server['SCRIPT_NAME'] ?? '')));

        if ($scriptDirectory !== '' && $scriptDirectory !== '/' && str_starts_with($path . '/', rtrim($scriptDirectory, '/') . '/')) {
            $path = substr($path, strlen(rtrim($scriptDirectory, '/'))) ?: '/';
        }

        return $path;
    }
}
