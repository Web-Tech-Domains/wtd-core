<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use WTD\Http\Client\HttpClient;
use WTD\Http\Client\HttpClientResponse;
use WTD\Http\Client\HttpTransport;

final class HttpClientTest extends TestCase
{
    public function testClientSendsJsonRequestsWithHeadersAndTimeout(): void
    {
        $transport = new RecordingTransport(new HttpClientResponse(
            201,
            ['Content-Type' => 'application/json'],
            '{"ok":true}',
        ));
        $client = (new HttpClient($transport))
            ->withHeaders(['Accept' => 'application/json'])
            ->timeout(2.5);

        $response = $client->post('https://api.example.test/users', [
            'headers' => ['X-Request-Id' => 'wtd-123'],
            'json' => ['name' => 'WTD'],
        ]);

        self::assertSame('POST', $transport->method);
        self::assertSame('https://api.example.test/users', $transport->url);
        self::assertSame('application/json', $transport->options['headers']['Accept']);
        self::assertSame('wtd-123', $transport->options['headers']['X-Request-Id']);
        self::assertSame(['name' => 'WTD'], $transport->options['json']);
        self::assertSame(2.5, $transport->options['timeout']);
        self::assertSame(201, $response->status());
        self::assertTrue($response->successful());
        self::assertFalse($response->failed());
        self::assertSame('application/json', $response->header('content-type'));
        self::assertSame(['ok' => true], $response->json());
    }

    public function testClientSupportsCommonHttpVerbs(): void
    {
        $transport = new RecordingTransport(new HttpClientResponse(204, [], ''));
        $client = new HttpClient($transport);

        $client->get('https://api.example.test/search', ['query' => ['q' => 'wtd']]);
        self::assertSame('GET', $transport->method);
        self::assertSame(['q' => 'wtd'], $transport->options['query']);

        $client->put('https://api.example.test/users/1', ['form' => ['name' => 'Core']]);
        self::assertSame('PUT', $transport->method);
        self::assertSame(['name' => 'Core'], $transport->options['form']);

        $client->patch('https://api.example.test/users/1', ['body' => 'active=true']);
        self::assertSame('PATCH', $transport->method);
        self::assertSame('active=true', $transport->options['body']);

        $client->delete('https://api.example.test/users/1');
        self::assertSame('DELETE', $transport->method);
    }

    public function testResponseMarksFailedStatusCodes(): void
    {
        $response = new HttpClientResponse(422, ['X-Error' => 'validation'], '{"message":"Invalid"}');

        self::assertTrue($response->failed());
        self::assertFalse($response->successful());
        self::assertSame('validation', $response->header('x-error'));
        self::assertSame('fallback', $response->header('missing', 'fallback'));
    }
}

final class RecordingTransport implements HttpTransport
{
    public string $method = '';

    public string $url = '';

    /**
     * @var array<string, mixed>
     */
    public array $options = [];

    public function __construct(private readonly HttpClientResponse $response)
    {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function send(string $method, string $url, array $options = []): HttpClientResponse
    {
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;

        return $this->response;
    }
}
