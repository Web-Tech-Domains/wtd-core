<?php

declare(strict_types=1);

namespace WTD\Http\Client;

use RuntimeException;

/**
 * Native PHP stream transport for environments without cURL/Guzzle.
 */
final class StreamHttpTransport implements HttpTransport
{
    /**
     * @param array<string, mixed> $options
     */
    public function send(string $method, string $url, array $options = []): HttpClientResponse
    {
        $url = $this->url($url, $options['query'] ?? []);
        $body = $this->body($options);
        $headers = $this->headers($options, $body);

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'ignore_errors' => true,
                'timeout' => is_numeric($options['timeout'] ?? null) ? (float) $options['timeout'] : 30.0,
            ],
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new RuntimeException(sprintf('HTTP request to [%s] failed.', $url));
        }

        /** @var list<string> $responseHeaders */
        $responseHeaders = $http_response_header;

        return new HttpClientResponse(
            $this->status($responseHeaders),
            $this->responseHeaders($responseHeaders),
            $response,
        );
    }

    /**
     * @param mixed $query
     */
    private function url(string $url, mixed $query): string
    {
        if (!is_array($query) || $query === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($query);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function body(array $options): string
    {
        if (array_key_exists('json', $options)) {
            return json_encode($options['json'], JSON_THROW_ON_ERROR);
        }

        if (isset($options['form']) && is_array($options['form'])) {
            return http_build_query($options['form']);
        }

        $body = $options['body'] ?? '';

        return is_scalar($body) ? (string) $body : '';
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return list<string>
     */
    private function headers(array $options, string $body): array
    {
        $headers = [];
        $input = $options['headers'] ?? [];

        if (is_array($input)) {
            foreach ($input as $name => $value) {
                if (is_string($name) && is_scalar($value)) {
                    $headers[$name] = (string) $value;
                }
            }
        }

        if (array_key_exists('json', $options) && !$this->hasHeader($headers, 'Content-Type')) {
            $headers['Content-Type'] = 'application/json';
        }

        if (isset($options['form']) && is_array($options['form']) && !$this->hasHeader($headers, 'Content-Type')) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        if ($body !== '' && !$this->hasHeader($headers, 'Content-Length')) {
            $headers['Content-Length'] = (string) strlen($body);
        }

        $lines = [];

        foreach ($headers as $name => $value) {
            $lines[] = $name . ': ' . $value;
        }

        return $lines;
    }

    /**
     * @param array<string, string> $headers
     */
    private function hasHeader(array $headers, string $name): bool
    {
        foreach ($headers as $header => $_) {
            if (strcasecmp($header, $name) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $headers
     */
    private function status(array $headers): int
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    /**
     * @param list<string> $headers
     *
     * @return array<string, string>
     */
    private function responseHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $header) {
            if (!str_contains($header, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $header, 2);
            $normalized[strtolower(trim($name))] = trim($value);
        }

        return $normalized;
    }
}
