<?php

declare(strict_types=1);

namespace WTD\DeveloperExperience;

use Throwable;

/**
 * Renders readable framework error pages without exposing details unless debug is enabled.
 */
final class ErrorPageRenderer
{
    public function render(Throwable $throwable, int $status, bool $debug): string
    {
        $title = $debug ? $throwable::class : $this->title($status);
        $message = $debug ? $throwable->getMessage() : $this->message($status);
        $details = $debug
            ? '<section class="debug"><h2>Debug details</h2><pre>' . $this->escape($throwable->getFile() . ':' . $throwable->getLine() . "\n\n" . $throwable->getTraceAsString()) . '</pre></section>'
            : '';

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>'
            . $this->escape($title)
            . '</title><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="icon" href="/favicon.svg" type="image/svg+xml"><link rel="manifest" href="/site.webmanifest"><style>'
            . $this->styles()
            . '</style></head><body><header class="topbar"><a class="brand" href="/"><img class="brand-logo" src="/favicon.svg" alt="WTD Core"><span>WTD Core</span></a><nav><a href="/">Home</a><a href="/health">Health</a><a href="/docs/api">API Docs</a></nav></header><main class="wrap"><section class="hero"><div><span class="badge">HTTP '
            . $status
            . '</span><h1>'
            . $this->escape($title)
            . '</h1><p>'
            . $this->escape($message)
            . '</p><div class="actions"><a class="primary" href="/">Back to Home</a><a href="/health">Check Status</a></div></div><aside class="panel"><span>Request status</span><strong>'
            . $status
            . '</strong><p>'
            . $this->escape($this->hint($status))
            . '</p></aside></section>'
            . $details
            . '</main></body></html>';
    }

    private function title(int $status): string
    {
        return match ($status) {
            404 => 'Page Not Found',
            405 => 'Method Not Allowed',
            default => 'Server Error',
        };
    }

    private function message(int $status): string
    {
        return match ($status) {
            404 => 'The requested page could not be found.',
            405 => 'The requested method is not allowed for this route.',
            default => 'Something went wrong while handling the request.',
        };
    }

    private function hint(int $status): string
    {
        return match ($status) {
            404 => 'Check the route file, enabled modules, and deployment rewrite rules.',
            405 => 'The route exists, but this HTTP method is not registered.',
            default => 'The error has been handled safely. Enable debug locally for trace details.',
        };
    }

    private function styles(): string
    {
        return <<<'CSS'
:root{color-scheme:light;--bg:#f6f8fb;--panel:#fff;--ink:#102032;--muted:#64748b;--line:#dbe3ec;--blue:#2563eb;--cyan:#0891b2;--green:#15803d;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink)}a{color:inherit;text-decoration:none}.topbar{min-height:72px;display:flex;align-items:center;justify-content:space-between;gap:18px;padding:0 max(24px,calc((100vw - 1120px)/2));border-bottom:1px solid var(--line);background:rgba(255,255,255,.94);backdrop-filter:blur(16px)}.brand{display:flex;align-items:center;gap:12px;font-weight:800;color:#172554}.brand-logo{width:38px;height:38px;display:block}nav{display:flex;flex-wrap:wrap;gap:10px}nav a{min-height:38px;display:inline-flex;align-items:center;padding:0 12px;border-radius:8px;color:var(--muted);font-size:14px;font-weight:800}nav a:hover{background:#eef6ff;color:var(--blue)}.wrap{width:min(1120px,calc(100% - 40px));margin:0 auto}.hero{min-height:calc(100vh - 72px);display:grid;grid-template-columns:minmax(0,1fr)360px;gap:34px;align-items:center;padding:70px 0}.badge{width:fit-content;display:inline-flex;align-items:center;min-height:34px;padding:0 12px;border:1px solid #bfdbfe;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:13px;font-weight:900}h1{max-width:760px;margin:18px 0 14px;font-size:clamp(42px,7vw,72px);line-height:1.04;letter-spacing:0}p{max-width:680px;margin:0;color:var(--muted);font-size:18px;line-height:1.7}.actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}.actions a{min-height:46px;display:inline-flex;align-items:center;justify-content:center;padding:0 16px;border:1px solid var(--line);border-radius:8px;background:#fff;font-weight:900}.actions .primary{border-color:var(--blue);background:var(--blue);color:#fff;box-shadow:0 16px 34px rgba(37,99,235,.22)}.panel{border:1px solid var(--line);border-radius:8px;background:var(--panel);padding:24px;box-shadow:0 20px 52px rgba(16,32,50,.08)}.panel span{display:block;color:var(--muted);font-size:14px;font-weight:800}.panel strong{display:block;margin:8px 0 12px;font-size:64px;line-height:1;color:var(--blue)}.panel p{font-size:15px}.debug{margin:0 0 56px;border:1px solid var(--line);border-radius:8px;background:#fff;padding:22px}.debug h2{margin:0 0 14px;font-size:20px}.debug pre{overflow:auto;margin:0;background:#0f172a;color:#e2e8f0;padding:16px;border-radius:8px;font-size:13px;line-height:1.6}@media(max-width:820px){.topbar{align-items:flex-start;flex-direction:column;padding:14px 20px}.hero{min-height:auto;grid-template-columns:1fr;padding:48px 0}.actions a{width:100%}}
CSS;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
