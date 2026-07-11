<?php

declare(strict_types=1);

namespace WTD\Security;

use WTD\Http\Request;

final class TrustedProxy
{
    /**
     * @param list<string> $trusted
     */
    public function clientIp(Request $request, array $trusted): string
    {
        $server = $request->server();
        $remote = (string) ($server['REMOTE_ADDR'] ?? '');

        if (!in_array($remote, $trusted, true)) {
            return $remote;
        }

        $forwarded = $request->header('x-forwarded-for');

        return $forwarded === null ? $remote : trim(explode(',', $forwarded)[0]);
    }
}
