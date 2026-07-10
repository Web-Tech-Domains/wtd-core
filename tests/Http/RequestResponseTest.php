<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use WTD\Cookie\Cookie;
use WTD\Http\Request;
use WTD\Http\Response;

final class RequestResponseTest extends TestCase
{
    public function testRequestExposesHttpState(): void
    {
        $request = new Request(
            'GET',
            '/users',
            ['accept' => 'application/json'],
            ['page' => 1],
            ['name' => 'Taylor'],
            ['theme' => 'dark'],
            ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'Example.test:8080'],
        );

        self::assertSame('GET', $request->method());
        self::assertSame('/users', $request->path());
        self::assertSame('application/json', $request->header('Accept'));
        self::assertSame(1, $request->query('page'));
        self::assertSame('Taylor', $request->input('name'));
        self::assertSame('dark', $request->cookie('theme'));
        self::assertSame('example.test', $request->host());
    }

    public function testResponseCanRepresentJson(): void
    {
        $response = Response::json(['status' => 'ok'], 201);

        self::assertSame(201, $response->status());
        self::assertSame('{"status":"ok"}', $response->content());
        self::assertSame('application/json', $response->headers()['Content-Type']);
    }

    public function testResponseCanRepresentRedirect(): void
    {
        $response = Response::redirect('/login');

        self::assertSame(302, $response->status());
        self::assertSame('/login', $response->headers()['Location']);
        self::assertSame('', $response->content());
    }

    public function testResponseCanAttachCookies(): void
    {
        $response = Response::make('OK')->withCookie(new Cookie('theme', 'dark'));

        self::assertSame('theme', $response->cookies()[0]->name());
    }

    public function testResponseCanSetHeaders(): void
    {
        $response = Response::make('OK')->withHeader('X-Test', 'true');

        self::assertSame('true', $response->headers()['X-Test']);
    }

    public function testResponseCanRepresentStream(): void
    {
        $response = Response::stream(static fn (): string => 'chunk');

        self::assertSame(200, $response->status());
        self::assertSame('chunk', $response->content());
        self::assertSame('text/plain; charset=UTF-8', $response->headers()['Content-Type']);
    }

    public function testResponseCanRepresentDownload(): void
    {
        $path = dirname(__DIR__) . '/tmp/downloads/report.txt';

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, 'report');

        $response = Response::download($path, 'report.txt');

        self::assertSame(200, $response->status());
        self::assertSame('report', $response->content());
        self::assertSame('application/octet-stream', $response->headers()['Content-Type']);
        self::assertSame('attachment; filename="report.txt"', $response->headers()['Content-Disposition']);
    }
}
