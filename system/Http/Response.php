<?php

declare(strict_types=1);

namespace WTD\Http;

use Closure;
use RuntimeException;
use WTD\Cookie\Cookie;

/**
 * Represents an outgoing HTTP response.
 */
final class Response
{
    /**
     * @param array<string, string> $headers
     * @param list<Cookie> $cookies
     * @param (Closure(): string)|null $stream
     */
    public function __construct(
        private string $content = '',
        private int $status = 200,
        private array $headers = ['Content-Type' => 'text/html; charset=UTF-8'],
        private array $cookies = [],
        private ?Closure $stream = null,
    ) {
    }

    /**
     * Create a plain text/html response.
     */
    public static function make(string $content, int $status = 200): self
    {
        return new self($content, $status);
    }

    /**
     * Create a JSON response.
     *
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $status = 200): self
    {
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR),
            $status,
            ['Content-Type' => 'application/json'],
        );
    }

    /**
     * Create a redirect response.
     */
    public static function redirect(string $location, int $status = 302): self
    {
        return new self('', $status, ['Location' => $location]);
    }

    /**
     * Create a file download response.
     */
    public static function download(string $path, ?string $name = null): self
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(sprintf('Download file [%s] is not readable.', $path));
        }

        $downloadName = $name ?? basename($path);

        return new self(
            '',
            200,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . addslashes($downloadName) . '"',
                'Content-Length' => (string) filesize($path),
            ],
            [],
            static fn (): string => (string) file_get_contents($path),
        );
    }

    /**
     * Create a streaming response.
     *
     * @param Closure(): string $stream
     */
    public static function stream(Closure $stream, int $status = 200, string $contentType = 'text/plain; charset=UTF-8'): self
    {
        return new self('', $status, ['Content-Type' => $contentType], [], $stream);
    }

    /**
     * Return the response status code.
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Return the response content.
     */
    public function content(): string
    {
        if ($this->stream !== null) {
            return ($this->stream)();
        }

        return $this->content;
    }

    /**
     * Return the response headers.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Set a response header.
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Attach a cookie to the response.
     */
    public function withCookie(Cookie $cookie): self
    {
        $this->cookies[] = $cookie;

        return $this;
    }

    /**
     * Return response cookies.
     *
     * @return list<Cookie>
     */
    public function cookies(): array
    {
        return $this->cookies;
    }

    /**
     * Send the response through PHP's SAPI.
     */
    public function send(): void
    {
        $hooksAvailable = isset($GLOBALS['wtd_app']);

        if ($hooksAvailable && $this->isRedirect()) {
            \do_action('app.before_redirect', $this);
            \do_action('app_hook_before_redirect', [
                'response' => $this,
                'status' => $this->status,
                'headers' => $this->headers,
                'location' => $this->headers['Location'] ?? null,
            ]);
        }

        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        foreach ($this->cookies as $cookie) {
            header('Set-Cookie: ' . $cookie->toHeader(), false);
        }

        echo $hooksAvailable ? \apply_filters('response.content', $this->content(), $this) : $this->content();
    }

    private function isRedirect(): bool
    {
        return $this->status >= 300 && $this->status < 400 && isset($this->headers['Location']);
    }
}
