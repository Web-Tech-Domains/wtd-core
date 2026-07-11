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
            '<style>%s</style><aside id="wtd-debug-toolbar" class="wtd-debug-toolbar" aria-label="WTD debug toolbar"><details><summary><span class="wtd-brand">WTD</span><span class="wtd-chip">%s</span><span class="wtd-route">%s</span><span class="wtd-status %s">%d</span><span>%.2f ms</span><span>%.2f MB</span><span>%d queries</span><span>%d headers</span></summary><div class="wtd-panel"><div class="wtd-panel-head"><strong>Debug Toolbar</strong><span>%s / %s / PHP %s</span></div><div class="wtd-sections">%s<section class="wtd-section"><h2>Profiler Marks</h2>%s</section></div></div></details></aside>',
            $this->styles(),
            $method,
            $path,
            $statusClass,
            $status,
            $profile['elapsed_ms'],
            $profile['memory_peak'] / 1024 / 1024,
            count($request->queryParams()),
            count($request->headers()),
            $this->escape((string) $this->config->get('app.env', 'development')),
            (bool) $this->config->get('app.debug', false) ? 'enabled' : 'disabled',
            PHP_VERSION,
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
                '<div class="wtd-row"><dt>%s</dt><dd>%s</dd></div>',
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
.wtd-debug-toolbar{position:fixed;right:14px;bottom:14px;z-index:2147483647;max-width:min(1040px,calc(100vw - 28px));font:12px/1.45 ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#e5e7eb}.wtd-debug-toolbar *{box-sizing:border-box}.wtd-debug-toolbar details{border:1px solid rgba(148,163,184,.32);border-radius:8px;background:#0f172a;box-shadow:0 18px 48px rgba(15,23,42,.26);overflow:hidden}.wtd-debug-toolbar summary{min-height:38px;display:flex;align-items:center;gap:7px;padding:6px 10px;cursor:pointer;list-style:none;overflow-x:auto;border-bottom:1px solid transparent}.wtd-debug-toolbar details[open] summary{border-bottom-color:rgba(148,163,184,.18)}.wtd-debug-toolbar summary::-webkit-details-marker{display:none}.wtd-brand{display:inline-flex;align-items:center;justify-content:center;width:38px;height:24px;border-radius:6px;background:#2563eb;color:#fff;font-weight:900;letter-spacing:0}.wtd-chip,.wtd-status,.wtd-debug-toolbar summary span:not(.wtd-brand){display:inline-flex;align-items:center;min-height:24px;border-radius:6px;background:#1e293b;padding:0 8px;font-weight:800;white-space:nowrap}.wtd-route{max-width:260px;overflow:hidden;text-overflow:ellipsis}.wtd-status.success{background:#064e3b;color:#a7f3d0}.wtd-status.warning{background:#713f12;color:#fde68a}.wtd-status.danger{background:#7f1d1d;color:#fecaca}.wtd-panel{width:min(1040px,calc(100vw - 28px));max-height:min(68vh,680px);overflow:auto;background:#0f172a;padding:0}.wtd-panel-head{position:sticky;top:0;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:10px 14px;border-bottom:1px solid rgba(148,163,184,.18);background:#111827}.wtd-panel-head strong{font-size:13px;color:#f8fafc}.wtd-panel-head span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#94a3b8;font-weight:800}.wtd-sections{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0}.wtd-section{min-width:0;border-right:1px solid rgba(148,163,184,.14);border-bottom:1px solid rgba(148,163,184,.14);background:#0f172a;padding:12px 14px}.wtd-section h2{margin:0 0 8px;color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:0}.wtd-section dl,.wtd-section ol{margin:0;padding:0;list-style:none}.wtd-row,.wtd-section li{display:grid;grid-template-columns:minmax(96px,.38fr)minmax(0,1fr);gap:14px;align-items:start;border-top:1px solid rgba(148,163,184,.12);padding:7px 0}.wtd-row:first-child,.wtd-section li:first-child{border-top:0}.wtd-panel dt,.wtd-section code{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#94a3b8;font-weight:800}.wtd-panel dd,.wtd-section li span{min-width:0;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#f8fafc;font-weight:800}.wtd-section code{color:#bfdbfe}.wtd-empty{margin:0;color:#94a3b8}@media(max-width:820px){.wtd-debug-toolbar{right:8px;bottom:8px;left:8px;max-width:none}.wtd-panel{width:100%}.wtd-sections{grid-template-columns:1fr}.wtd-section{border-right:0}.wtd-route{max-width:170px}.wtd-row,.wtd-section li{grid-template-columns:1fr;gap:3px}}@media(max-width:520px){.wtd-panel-head{align-items:flex-start;flex-direction:column;gap:4px}}
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
