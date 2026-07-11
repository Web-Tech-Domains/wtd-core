<?php

declare(strict_types=1);

namespace WTD\Security;

use Closure;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;

final class VerifyCsrfToken implements Middleware
{
    /**
     * @var list<string>
     */
    private array $safeMethods = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private readonly CsrfTokenManager $tokens)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), $this->safeMethods, true)) {
            return $next($request);
        }

        $token = $request->header('x-csrf-token') ?? (is_string($request->input('_token')) ? $request->input('_token') : null);

        if (!$this->tokens->validate($token)) {
            return Response::make('CSRF token mismatch', 419);
        }

        return $next($request);
    }
}
