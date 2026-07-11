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
        $toolbar = $this->toolbar($request, $response, $profile);

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
    private function toolbar(Request $request, Response $response, array $profile): string
    {
        $method = $this->escape($request->method());
        $path = $this->escape($request->path());
        $status = $response->status();
        $statusClass = $status >= 500 ? 'danger' : ($status >= 400 ? 'warning' : 'success');
        $marks = $this->marks($profile['marks']);
        $sections = $this->sections($request, $response, $profile);

        return sprintf(
            '<style>%s</style><aside id="wtd-debug-toolbar" class="wtd-debug-toolbar" aria-label="WTD debug toolbar"><details><summary><span class="wtd-brand">WTD</span><span class="wtd-chip">%s</span><span class="wtd-route">%s</span><span class="wtd-status %s">%d</span><span>%.2f ms</span><span>%.2f MB</span><span>%d queries</span><span>%d headers</span></summary><div class="wtd-panel"><dl class="wtd-metrics"><div><dt>Method</dt><dd>%s</dd></div><div><dt>Path</dt><dd>%s</dd></div><div><dt>Status</dt><dd>%d</dd></div><div><dt>Memory peak</dt><dd>%.2f MB</dd></div><div><dt>Environment</dt><dd>%s</dd></div><div><dt>Debug</dt><dd>%s</dd></div></dl><div class="wtd-sections">%s<section class="wtd-section"><h2>Profiler Marks</h2>%s</section></div></div></details></aside>',
            $this->styles(),
            $method,
            $path,
            $statusClass,
            $status,
            $profile['elapsed_ms'],
            $profile['memory_peak'] / 1024 / 1024,
            count($request->queryParams()),
            count($request->headers()),
            $method,
            $path,
            $status,
            $profile['memory_peak'] / 1024 / 1024,
            $this->escape((string) $this->config->get('app.env', 'development')),
            (bool) $this->config->get('app.debug', false) ? 'enabled' : 'disabled',
            $sections,
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

    /**
     * @param array{elapsed_ms: float, memory_usage: int, memory_peak: int, marks: list<array{name: string, elapsed_ms: float}>} $profile
     */
    private function sections(Request $request, Response $response, array $profile): string
    {
        return $this->section('Request', [
            'Method' => $request->method(),
            'Path' => $request->path(),
            'Host' => $request->host(),
            'IP' => (string) ($request->server()['REMOTE_ADDR'] ?? 'n/a'),
            'User agent' => $request->header('user-agent', 'n/a') ?? 'n/a',
        ])
            . $this->section('Response', [
                'Status' => (string) $response->status(),
                'Content-Type' => $response->headers()['Content-Type'] ?? 'n/a',
                'Headers' => (string) count($response->headers()),
                'Cookies' => (string) count($response->cookies()),
            ])
            . $this->section('Runtime', [
                'PHP' => PHP_VERSION,
                'Environment' => (string) $this->config->get('app.env', 'development'),
                'Debug' => (bool) $this->config->get('app.debug', false) ? 'enabled' : 'disabled',
                'Peak memory' => sprintf('%.2f MB', $profile['memory_peak'] / 1024 / 1024),
                'Elapsed' => sprintf('%.2f ms', $profile['elapsed_ms']),
            ])
            . $this->section('Config', [
                'App name' => (string) $this->config->get('app.name', 'WTD Core'),
                'Database' => (string) $this->config->get('database.default', 'n/a'),
                'Cache' => (string) $this->config->get('cache.default', 'n/a'),
                'Queue' => (string) $this->config->get('queue.default', 'n/a'),
                'Filesystem' => (string) $this->config->get('filesystems.default', 'n/a'),
            ])
            . $this->section('Input', [
                'Query parameters' => (string) count($request->queryParams()),
                'Body fields' => (string) max(0, count($request->all()) - count($request->queryParams())),
                'Total input' => (string) count($request->all()),
            ])
            . $this->table('Headers', $request->headers())
            . $this->table('Cookies', $request->cookies(), true)
            . $this->table('Server', $this->serverSummary($request));
    }

    /**
     * @param array<string, string> $values
     */
    private function section(string $title, array $values): string
    {
        $rows = '';

        foreach ($values as $label => $value) {
            $rows .= sprintf(
                '<div><dt>%s</dt><dd>%s</dd></div>',
                $this->escape($label),
                $this->escape($value),
            );
        }

        return sprintf('<section class="wtd-section"><h2>%s</h2><dl>%s</dl></section>', $this->escape($title), $rows);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function table(string $title, array $values, bool $maskValues = false): string
    {
        if ($values === []) {
            return sprintf('<section class="wtd-section"><h2>%s</h2><p class="wtd-empty">None.</p></section>', $this->escape($title));
        }

        $rows = '';

        foreach ($values as $key => $value) {
            $rows .= sprintf(
                '<li><code>%s</code><span>%s</span></li>',
                $this->escape((string) $key),
                $this->escape($maskValues ? '[hidden]' : $this->stringValue($value)),
            );
        }

        return sprintf('<section class="wtd-section"><h2>%s</h2><ol>%s</ol></section>', $this->escape($title), $rows);
    }

    /**
     * @return array<string, mixed>
     */
    private function serverSummary(Request $request): array
    {
        $server = $request->server();

        return [
            'REQUEST_METHOD' => $server['REQUEST_METHOD'] ?? $request->method(),
            'REQUEST_URI' => $server['REQUEST_URI'] ?? $request->path(),
            'SERVER_NAME' => $server['SERVER_NAME'] ?? 'n/a',
            'SERVER_SOFTWARE' => $server['SERVER_SOFTWARE'] ?? 'n/a',
            'REMOTE_ADDR' => $server['REMOTE_ADDR'] ?? 'n/a',
        ];
    }

    private function styles(): string
    {
        return <<<'CSS'
.wtd-debug-toolbar{position:fixed;right:16px;bottom:16px;z-index:2147483647;max-width:min(980px,calc(100vw - 32px));font:12px/1.45 ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#e5e7eb}.wtd-debug-toolbar *{box-sizing:border-box}.wtd-debug-toolbar details{border:1px solid rgba(148,163,184,.34);border-radius:8px;background:#111827;box-shadow:0 18px 48px rgba(15,23,42,.24);overflow:hidden}.wtd-debug-toolbar summary{min-height:40px;display:flex;align-items:center;gap:8px;padding:7px 10px;cursor:pointer;list-style:none;overflow-x:auto}.wtd-debug-toolbar summary::-webkit-details-marker{display:none}.wtd-brand{display:inline-flex;align-items:center;justify-content:center;width:38px;height:26px;border-radius:6px;background:#2563eb;color:#fff;font-weight:900;letter-spacing:0}.wtd-chip,.wtd-status,.wtd-debug-toolbar summary span:not(.wtd-brand){display:inline-flex;align-items:center;min-height:26px;border-radius:6px;background:#1f2937;padding:0 8px;font-weight:800;white-space:nowrap}.wtd-route{max-width:260px;overflow:hidden;text-overflow:ellipsis}.wtd-status.success{background:#064e3b;color:#a7f3d0}.wtd-status.warning{background:#713f12;color:#fde68a}.wtd-status.danger{background:#7f1d1d;color:#fecaca}.wtd-panel{width:min(980px,calc(100vw - 32px));max-height:min(72vh,720px);overflow:auto;border-top:1px solid rgba(148,163,184,.22);background:#0f172a;padding:14px}.wtd-metrics,.wtd-section dl{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0}.wtd-metrics{margin-bottom:14px}.wtd-panel dl div{min-width:0;border:1px solid rgba(148,163,184,.2);border-radius:8px;background:#111827;padding:10px}.wtd-panel dt{margin:0 0 5px;color:#94a3b8;font-weight:800}.wtd-panel dd{margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#f8fafc;font-weight:900}.wtd-sections{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.wtd-section{min-width:0;border:1px solid rgba(148,163,184,.16);border-radius:8px;background:#111827;padding:12px}.wtd-section h2{margin:0 0 10px;color:#cbd5e1;font-size:12px;text-transform:uppercase;letter-spacing:0}.wtd-section ol{max-height:190px;overflow:auto;margin:0;padding:0;list-style:none}.wtd-section li{display:flex;justify-content:space-between;gap:12px;border-top:1px solid rgba(148,163,184,.14);padding:7px 0}.wtd-section code{min-width:0;overflow:hidden;text-overflow:ellipsis;color:#bfdbfe}.wtd-section li span{max-width:62%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#e2e8f0}.wtd-empty{margin:0;color:#94a3b8}@media(max-width:820px){.wtd-debug-toolbar{right:8px;bottom:8px;left:8px;max-width:none}.wtd-panel{width:100%}.wtd-metrics,.wtd-section dl,.wtd-sections{grid-template-columns:1fr}.wtd-route{max-width:170px}}
CSS;
    }

    private function stringValue(mixed $value): string
    {
        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
