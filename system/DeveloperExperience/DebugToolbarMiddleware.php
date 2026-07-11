<?php

declare(strict_types=1);

namespace WTD\DeveloperExperience;

use Closure;
use WTD\Config\Repository;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;

/**
 * Appends a compact debug toolbar to HTML responses when explicitly enabled.
 */
final class DebugToolbarMiddleware implements Middleware
{
    public function __construct(
        private readonly Repository $config,
        private readonly Profiler $profiler,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->profiler->mark('request.start');
        $response = $next($request);
        $this->profiler->mark('request.end');

        if (!$this->enabled() || !$this->isHtml($response)) {
            return $response;
        }

        $profile = $this->profiler->snapshot();
        $toolbar = $this->toolbar($request, $response->status(), $profile);

        $decorated = new Response(
            $response->content() . $toolbar,
            $response->status(),
            $response->headers(),
            $response->cookies(),
        );

        return $decorated->withHeader('X-WTD-Profile-Time', sprintf('%.3fms', $profile['elapsed_ms']));
    }

    private function enabled(): bool
    {
        return (bool) $this->config->get('developer.enabled', false)
            && (bool) $this->config->get('developer.debug_toolbar', false);
    }

    private function isHtml(Response $response): bool
    {
        $contentType = $response->headers()['Content-Type'] ?? '';

        return str_contains(strtolower($contentType), 'text/html');
    }

    /**
     * @param array{elapsed_ms: float, memory_usage: int, memory_peak: int, marks: list<array{name: string, elapsed_ms: float}>} $profile
     */
    private function toolbar(Request $request, int $status, array $profile): string
    {
        $path = htmlspecialchars($request->method() . ' ' . $request->path(), ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<div id="wtd-debug-toolbar" style="position:fixed;right:12px;bottom:12px;z-index:2147483647;font:12px/1.4 system-ui,sans-serif;background:#111827;color:#f9fafb;border:1px solid #374151;border-radius:6px;padding:8px 10px;box-shadow:0 8px 24px rgba(0,0,0,.25)">WTD %s | %d | %.2f ms | %.2f MB</div>',
            $path,
            $status,
            $profile['elapsed_ms'],
            $profile['memory_peak'] / 1024 / 1024,
        );
    }
}
