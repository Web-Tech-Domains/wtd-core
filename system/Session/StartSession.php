<?php

declare(strict_types=1);

namespace WTD\Session;

use Closure;
use InvalidArgumentException;
use WTD\Cookie\Cookie;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;

/**
 * Starts and saves the framework session for each request.
 */
final class StartSession implements Middleware
{
    public function __construct(
        private readonly SessionStore $session,
        private readonly string $cookieName = 'WTDSESSID',
    ) {
    }

    /**
     * Handle the request with session state.
     *
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->session->start($request->cookie($this->cookieName));
        } catch (InvalidArgumentException) {
            $this->session->start();
        }

        $response = $next($request);
        $this->session->save();

        return $response->withCookie(new Cookie($this->cookieName, $this->session->id()));
    }
}
