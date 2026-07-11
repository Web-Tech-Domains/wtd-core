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
        $history = $this->history($request, $response);

        return sprintf(
            '<style>%s</style><aside id="wtd-debug-toolbar" class="wtd-debug-toolbar" aria-label="WTD debug toolbar"><input type="radio" name="wtd-debug-tab" id="wtd-tab-history" checked><input type="radio" name="wtd-debug-tab" id="wtd-tab-vars"><input type="radio" name="wtd-debug-tab" id="wtd-tab-events"><input type="radio" name="wtd-debug-tab" id="wtd-tab-routes"><input type="radio" name="wtd-debug-tab" id="wtd-tab-files"><input type="radio" name="wtd-debug-tab" id="wtd-tab-profiler"><input type="checkbox" id="wtd-debug-close"><div class="wtd-panels"><section class="wtd-tab-panel wtd-history"><h2>History</h2>%s</section><section class="wtd-tab-panel wtd-vars"><h2>Vars</h2><div class="wtd-sections">%s</div></section><section class="wtd-tab-panel wtd-events"><h2>Events</h2><p class="wtd-empty">No event timeline captured for this request.</p></section><section class="wtd-tab-panel wtd-routes"><h2>Routes</h2>%s</section><section class="wtd-tab-panel wtd-files"><h2>Files</h2>%s</section><section class="wtd-tab-panel wtd-profiler"><h2>Profiler Marks</h2>%s</section></div><nav class="wtd-bar"><span class="wtd-brand">WTD</span><span class="wtd-chip">%s</span><span class="wtd-route">%s</span><span class="wtd-status %s">%d</span><span>%.2f ms</span><span>%.2f MB</span><label for="wtd-tab-profiler">Profiler <b>%d</b></label><label for="wtd-tab-files">Files <b>%d</b></label><label for="wtd-tab-routes">Routes <b>%d</b></label><label for="wtd-tab-events">Events <b>0</b></label><label for="wtd-tab-history">History <b>1</b></label><label for="wtd-tab-vars">Vars</label><label class="wtd-close" for="wtd-debug-close">x</label></nav></aside>',
            $this->styles(),
            $history,
            $sections,
            $this->section('Current Route', [
                'Method' => $request->method(),
                'Path' => $request->path(),
                'Status' => (string) $response->status(),
            ]),
            $this->table('Loaded Configuration', [
                'app' => $this->config->get('app.name', 'WTD Core'),
                'environment' => $this->config->get('app.env', 'development'),
                'database' => $this->config->get('database.default', 'n/a'),
                'cache' => $this->config->get('cache.default', 'n/a'),
            ]),
            $marks,
            $method,
            $path,
            $statusClass,
            $status,
            $profile['elapsed_ms'],
            $profile['memory_peak'] / 1024 / 1024,
            count($profile['marks']),
            count(get_included_files()),
            1,
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

    private function history(Request $request, Response $response): string
    {
        $requestedWith = $request->header('x-requested-with') ?? '';

        return sprintf(
            '<table><thead><tr><th>Action</th><th>Datetime</th><th>Status</th><th>Method</th><th>URL</th><th>Content-Type</th><th>Is AJAX?</th></tr></thead><tbody><tr><td><button type="button">Load</button></td><td>%s</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr></tbody></table>',
            $this->escape((new \DateTimeImmutable())->format('Y-m-d H:i:s.u')),
            $response->status(),
            $this->escape($request->method()),
            $this->escape($this->currentUrl($request)),
            $this->escape($response->headers()['Content-Type'] ?? 'n/a'),
            strtolower($requestedWith) === 'xmlhttprequest' ? 'Yes' : 'No',
        );
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

    private function currentUrl(Request $request): string
    {
        $server = $request->server();
        $scheme = ((string) ($server['HTTPS'] ?? '')) !== '' && strtolower((string) $server['HTTPS']) !== 'off'
            ? 'https'
            : 'http';
        $host = $request->header('host') ?? (string) ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost');

        return $scheme . '://' . $host . $request->path();
    }

    private function styles(): string
    {
        return <<<'CSS'
.wtd-debug-toolbar{position:fixed;left:4px;right:4px;bottom:4px;z-index:2147483647;font:13px/1.45 Arial,ui-sans-serif,system-ui,sans-serif;color:#172033}.wtd-debug-toolbar *{box-sizing:border-box}.wtd-debug-toolbar input{position:absolute;opacity:0;pointer-events:none}.wtd-panels{display:none;max-height:58vh;overflow:auto;border:1px solid #d7dce3;border-bottom:0;border-radius:8px 8px 0 0;background:#fff;box-shadow:0 -12px 36px rgba(15,23,42,.12)}#wtd-debug-close:checked~.wtd-panels,#wtd-debug-close:checked~.wtd-bar{display:none}.wtd-tab-panel{display:none;padding:24px 28px}.wtd-tab-panel>h2{margin:0 0 20px;font-size:18px;color:#172033}.wtd-debug-toolbar table{width:100%;border-collapse:collapse;background:#fff}.wtd-debug-toolbar th{padding:11px 12px;background:#f4f4f5;color:#444;text-align:left;font-weight:700}.wtd-debug-toolbar td{padding:12px;border-top:1px solid #eef0f3;white-space:nowrap}.wtd-history tbody tr:first-child{background:#ffd09b}.wtd-debug-toolbar button{border:1px solid #6b7280;border-radius:4px;background:#fff;padding:1px 8px;color:#111827}.wtd-bar{min-height:38px;display:flex;align-items:center;gap:12px;overflow-x:auto;border:1px solid #d7dce3;border-radius:0 0 8px 8px;background:rgba(255,255,255,.96);box-shadow:0 -8px 28px rgba(15,23,42,.10);padding:0 12px}.wtd-brand{display:inline-flex;align-items:center;justify-content:center;width:42px;height:25px;border-radius:6px;background:#2563eb;color:#fff;font-weight:900}.wtd-chip,.wtd-status,.wtd-bar>span:not(.wtd-brand){display:inline-flex;align-items:center;min-height:24px;border-radius:5px;background:#eef2f7;padding:0 8px;font-weight:700;white-space:nowrap}.wtd-route{max-width:220px;overflow:hidden;text-overflow:ellipsis}.wtd-status.success{background:#dcfce7;color:#166534}.wtd-status.warning{background:#fef3c7;color:#92400e}.wtd-status.danger{background:#fee2e2;color:#991b1b}.wtd-bar label{min-height:38px;display:inline-flex;align-items:center;gap:6px;padding:0 10px;border-left:1px solid transparent;border-right:1px solid transparent;white-space:nowrap;cursor:pointer;color:#374151}.wtd-bar label:hover{background:#f3f4f6}.wtd-bar b{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;border-radius:999px;background:#e63f19;color:#fff;font-size:11px}.wtd-close{margin-left:auto;font-size:18px;font-weight:700}.wtd-sections{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));border:1px solid #e5e7eb}.wtd-section{min-width:0;padding:14px 16px;border-right:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb}.wtd-section h2{margin:0 0 10px;font-size:13px;text-transform:uppercase;color:#374151}.wtd-section dl,.wtd-section ol{margin:0;padding:0;list-style:none}.wtd-row,.wtd-section li{display:grid;grid-template-columns:minmax(120px,.34fr)minmax(0,1fr);gap:14px;padding:7px 0;border-top:1px solid #eef0f3}.wtd-row:first-child,.wtd-section li:first-child{border-top:0}.wtd-section dt,.wtd-section code{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#6b7280;font-weight:700}.wtd-section dd,.wtd-section li span{min-width:0;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#111827;font-weight:600}.wtd-empty{margin:0;color:#6b7280}#wtd-tab-history:checked~.wtd-panels .wtd-history,#wtd-tab-vars:checked~.wtd-panels .wtd-vars,#wtd-tab-events:checked~.wtd-panels .wtd-events,#wtd-tab-routes:checked~.wtd-panels .wtd-routes,#wtd-tab-files:checked~.wtd-panels .wtd-files,#wtd-tab-profiler:checked~.wtd-panels .wtd-profiler{display:block}#wtd-tab-history:checked~.wtd-panels,#wtd-tab-vars:checked~.wtd-panels,#wtd-tab-events:checked~.wtd-panels,#wtd-tab-routes:checked~.wtd-panels,#wtd-tab-files:checked~.wtd-panels,#wtd-tab-profiler:checked~.wtd-panels{display:block}#wtd-tab-history:checked~.wtd-bar label[for=wtd-tab-history],#wtd-tab-vars:checked~.wtd-bar label[for=wtd-tab-vars],#wtd-tab-events:checked~.wtd-bar label[for=wtd-tab-events],#wtd-tab-routes:checked~.wtd-bar label[for=wtd-tab-routes],#wtd-tab-files:checked~.wtd-bar label[for=wtd-tab-files],#wtd-tab-profiler:checked~.wtd-bar label[for=wtd-tab-profiler]{background:#e5e7eb;color:#111827}@media(max-width:820px){.wtd-tab-panel{padding:18px}.wtd-sections{grid-template-columns:1fr}.wtd-section{border-right:0}.wtd-row,.wtd-section li{grid-template-columns:1fr;gap:3px}.wtd-debug-toolbar td,.wtd-debug-toolbar th{white-space:normal}.wtd-route{max-width:150px}}
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
