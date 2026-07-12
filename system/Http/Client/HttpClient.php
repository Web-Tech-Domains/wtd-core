<?php

declare(strict_types=1);

namespace WTD\Http\Client;

/**
 * Small outbound HTTP client with a Guzzle-like developer API.
 */
final class HttpClient
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly HttpTransport $transport = new StreamHttpTransport(),
        private readonly array $headers = [],
        private readonly ?float $timeout = null,
    ) {
    }

    /**
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        return new self($this->transport, array_replace($this->headers, $headers), $this->timeout);
    }

    public function timeout(float $seconds): self
    {
        return new self($this->transport, $this->headers, $seconds);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function get(string $url, array $options = []): HttpClientResponse
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function post(string $url, array $options = []): HttpClientResponse
    {
        return $this->request('POST', $url, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function put(string $url, array $options = []): HttpClientResponse
    {
        return $this->request('PUT', $url, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function patch(string $url, array $options = []): HttpClientResponse
    {
        return $this->request('PATCH', $url, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function delete(string $url, array $options = []): HttpClientResponse
    {
        return $this->request('DELETE', $url, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $url, array $options = []): HttpClientResponse
    {
        $options['headers'] = array_replace($this->headers, $this->headers($options['headers'] ?? []));

        if ($this->timeout !== null && !isset($options['timeout'])) {
            $options['timeout'] = $this->timeout;
        }

        return $this->transport->send(strtoupper($method), $url, $options);
    }

    /**
     * @param mixed $headers
     *
     * @return array<string, string>
     */
    private function headers(mixed $headers): array
    {
        if (!is_array($headers)) {
            return [];
        }

        $normalized = [];

        foreach ($headers as $name => $value) {
            if (is_string($name) && is_scalar($value)) {
                $normalized[$name] = (string) $value;
            }
        }

        return $normalized;
    }
}
