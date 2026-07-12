<?php

declare(strict_types=1);

namespace WTD\Http\Client;

interface HttpTransport
{
    /**
     * @param array<string, mixed> $options
     */
    public function send(string $method, string $url, array $options = []): HttpClientResponse;
}
