<?php

declare(strict_types=1);

namespace Tests\WebSocket;

use PHPUnit\Framework\TestCase;
use WTD\Http\Request;
use WTD\WebSocket\WebSocketChannelManager;
use WTD\WebSocket\WebSocketConnection;
use WTD\WebSocket\WebSocketFrame;
use WTD\WebSocket\WebSocketHandshake;

final class WebSocketTest extends TestCase
{
    public function testHandshakeBuildsUpgradeResponse(): void
    {
        $handshake = new WebSocketHandshake();
        $request = new Request('GET', '/ws', headers: [
            'upgrade' => 'websocket',
            'sec-websocket-key' => 'dGhlIHNhbXBsZSBub25jZQ==',
        ]);

        $response = $handshake->response($request);

        self::assertTrue($handshake->accepts($request));
        self::assertSame(101, $response->status());
        self::assertSame('s3pPLMBiTxaQ9kYGzzhZRbK+xOo=', $response->headers()['Sec-WebSocket-Accept']);
    }

    public function testFrameEncodesAndDecodesTextPayloads(): void
    {
        $frame = new WebSocketFrame();

        self::assertSame('hello', $frame->decodeText($frame->encodeText('hello')));
    }

    public function testChannelManagerTracksSubscribers(): void
    {
        $manager = new WebSocketChannelManager();
        $connection = new WebSocketConnection('one');

        $manager->subscribe('chat', $connection);
        self::assertSame([$connection], $manager->subscribers('chat'));

        $manager->unsubscribe('chat', $connection);
        self::assertSame([], $manager->subscribers('chat'));
    }
}
