<?php

declare(strict_types=1);

namespace Tests\Session;

use PHPUnit\Framework\TestCase;
use WTD\Filesystem\Filesystem;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Session\SessionStore;
use WTD\Session\StartSession;

final class SessionStoreTest extends TestCase
{
    public function testSessionStorePersistsData(): void
    {
        $store = new SessionStore(new Filesystem(), dirname(__DIR__) . '/tmp/sessions');
        $store->start('known-session');
        $store->put('name', 'WTD');
        $store->save();

        $loaded = new SessionStore(new Filesystem(), dirname(__DIR__) . '/tmp/sessions');
        $loaded->start('known-session');

        self::assertSame('WTD', $loaded->get('name'));
    }

    public function testStartSessionMiddlewareSavesAndSetsCookie(): void
    {
        $store = new SessionStore(new Filesystem(), dirname(__DIR__) . '/tmp/sessions');
        $middleware = new StartSession($store);

        $response = $middleware->handle(
            new Request('GET', '/'),
            static function (Request $request) use ($store): Response {
                $store->put('visited', true);

                return Response::make('OK');
            },
        );

        self::assertSame('OK', $response->content());
        self::assertCount(1, $response->cookies());
        self::assertTrue($store->get('visited'));
    }
}
