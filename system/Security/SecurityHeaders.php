<?php

declare(strict_types=1);

namespace WTD\Security;

use WTD\Http\Response;

final class SecurityHeaders
{
    public function apply(Response $response): Response
    {
        return $response
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Content-Security-Policy', "default-src 'self'");
    }
}
