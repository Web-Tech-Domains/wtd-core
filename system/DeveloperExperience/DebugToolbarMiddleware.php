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
            $this->injectToolbar($response->content(), $toolbar),
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
        $method = $this->escape($request->method());
        $path = $this->escape($request->path());
        $statusClass = $status >= 500 ? 'danger' : ($status >= 400 ? 'warning' : 'success');
        $marks = $this->marks($profile['marks']);

        return sprintf(
            '<style>%s</style><aside id="wtd-debug-toolbar" class="wtd-debug-toolbar" aria-label="WTD debug toolbar"><details><summary><span class="wtd-brand">WTD</span><span class="wtd-chip">%s</span><span class="wtd-route">%s</span><span class="wtd-status %s">%d</span><span>%.2f ms</span><span>%.2f MB</span></summary><div class="wtd-panel"><dl><div><dt>Method</dt><dd>%s</dd></div><div><dt>Path</dt><dd>%s</dd></div><div><dt>Status</dt><dd>%d</dd></div><div><dt>Memory peak</dt><dd>%.2f MB</dd></div></dl><section><h2>Profiler Marks</h2>%s</section></div></details></aside>',
            $this->styles(),
            $method,
            $path,
            $statusClass,
            $status,
            $profile['elapsed_ms'],
            $profile['memory_peak'] / 1024 / 1024,
            $method,
            $path,
            $status,
            $profile['memory_peak'] / 1024 / 1024,
            $marks,
        );
    }

    private function injectToolbar(string $content, string $toolbar): string
    {
        if (stripos($content, '</body>') === false) {
            return $content . $toolbar;
        }

        return (string) preg_replace('/<\/body>/i', $toolbar . '</body>', $content, 1);
    }

    /**
     * @param list<array{name: string, elapsed_ms: float}> $marks
     */
    private function marks(array $marks): string
    {
        if ($marks === []) {
            return '<p class="wtd-empty">No marks recorded.</p>';
        }

        $items = '';

        foreach ($marks as $mark) {
            $items .= sprintf(
                '<li><code>%s</code><span>%.2f ms</span></li>',
                $this->escape($mark['name']),
                $mark['elapsed_ms'],
            );
        }

        return '<ol>' . $items . '</ol>';
    }

    private function styles(): string
    {
        return <<<'CSS'
.wtd-debug-toolbar{position:fixed;right:16px;bottom:16px;z-index:2147483647;max-width:min(760px,calc(100vw - 32px));font:12px/1.45 ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#e5e7eb}.wtd-debug-toolbar *{box-sizing:border-box}.wtd-debug-toolbar details{border:1px solid rgba(148,163,184,.34);border-radius:8px;background:#111827;box-shadow:0 18px 48px rgba(15,23,42,.24);overflow:hidden}.wtd-debug-toolbar summary{min-height:38px;display:flex;align-items:center;gap:8px;padding:7px 10px;cursor:pointer;list-style:none}.wtd-debug-toolbar summary::-webkit-details-marker{display:none}.wtd-brand{display:inline-flex;align-items:center;justify-content:center;width:34px;height:24px;border-radius:6px;background:#2563eb;color:#fff;font-weight:900;letter-spacing:0}.wtd-chip,.wtd-status,.wtd-debug-toolbar summary span:not(.wtd-brand){display:inline-flex;align-items:center;min-height:24px;border-radius:6px;background:#1f2937;padding:0 8px;font-weight:800;white-space:nowrap}.wtd-route{max-width:280px;overflow:hidden;text-overflow:ellipsis}.wtd-status.success{background:#064e3b;color:#a7f3d0}.wtd-status.warning{background:#713f12;color:#fde68a}.wtd-status.danger{background:#7f1d1d;color:#fecaca}.wtd-panel{width:min(760px,calc(100vw - 32px));border-top:1px solid rgba(148,163,184,.22);background:#0f172a;padding:14px}.wtd-panel dl{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 0 14px}.wtd-panel dl div{min-width:0;border:1px solid rgba(148,163,184,.2);border-radius:8px;background:#111827;padding:10px}.wtd-panel dt{margin:0 0 5px;color:#94a3b8;font-weight:800}.wtd-panel dd{margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#f8fafc;font-weight:900}.wtd-panel h2{margin:0 0 8px;color:#cbd5e1;font-size:12px;text-transform:uppercase;letter-spacing:0}.wtd-panel ol{max-height:180px;overflow:auto;margin:0;padding:0;list-style:none}.wtd-panel li{display:flex;justify-content:space-between;gap:12px;border-top:1px solid rgba(148,163,184,.14);padding:7px 0}.wtd-panel code{min-width:0;overflow:hidden;text-overflow:ellipsis;color:#bfdbfe}.wtd-empty{margin:0;color:#94a3b8}@media(max-width:720px){.wtd-debug-toolbar{right:8px;bottom:8px;left:8px;max-width:none}.wtd-debug-toolbar summary{overflow-x:auto}.wtd-panel{width:100%}.wtd-panel dl{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:420px){.wtd-panel dl{grid-template-columns:1fr}.wtd-route{max-width:150px}}
CSS;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
