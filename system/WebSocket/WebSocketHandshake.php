<?php

declare(strict_types=1);

namespace WTD\WebSocket;

use WTD\Http\Request;
use WTD\Http\Response;

/**
 * Builds RFC 6455 upgrade handshake responses.
 */
final class WebSocketHandshake
{
    public function accepts(Request $request): bool
    {
        return strtolower((string) $request->header('upgrade', '')) === 'websocket'
            && $request->header('sec-websocket-key') !== null;
    }

    public function response(Request $request): Response
    {
        $key = (string) $request->header('sec-websocket-key', '');
        $accept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

        return Response::make('', 101)
            ->withHeader('Upgrade', 'websocket')
            ->withHeader('Connection', 'Upgrade')
            ->withHeader('Sec-WebSocket-Accept', $accept);
    }
}
