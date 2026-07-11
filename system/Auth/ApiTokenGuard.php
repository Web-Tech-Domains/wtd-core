<?php

declare(strict_types=1);

namespace WTD\Auth;

use WTD\Http\Request;

final class ApiTokenGuard
{
    public function __construct(private readonly ApiTokenStore $tokens)
    {
    }

    public function user(Request $request): ?Authenticatable
    {
        $header = $request->header('authorization', '');

        if (!str_starts_with((string) $header, 'Bearer ')) {
            return null;
        }

        return $this->tokens->userForToken(substr((string) $header, 7));
    }
}
