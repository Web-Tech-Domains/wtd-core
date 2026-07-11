<?php

declare(strict_types=1);

namespace WTD\WebSocket;

/**
 * Encodes and decodes small text WebSocket frames.
 */
final class WebSocketFrame
{
    public function encodeText(string $payload): string
    {
        $length = strlen($payload);

        if ($length < 126) {
            return chr(0x81) . chr($length) . $payload;
        }

        return chr(0x81) . chr(126) . pack('n', $length) . $payload;
    }

    public function decodeText(string $frame): string
    {
        if (strlen($frame) < 2) {
            return '';
        }

        $length = ord($frame[1]) & 127;
        $offset = 2;

        if ($length === 126) {
            $length = unpack('n', substr($frame, 2, 2))[1] ?? 0;
            $offset = 4;
        }

        return substr($frame, $offset, $length);
    }
}
