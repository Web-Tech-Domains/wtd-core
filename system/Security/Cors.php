<?php

declare(strict_types=1);

namespace WTD\Security;

use WTD\Http\Response;

final class Cors
{
    public function apply(Response $response, string $origin = '*'): Response
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-CSRF-Token');
    }
}
