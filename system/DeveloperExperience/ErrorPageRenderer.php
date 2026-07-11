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
            ? '<pre>' . $this->escape($throwable->getFile() . ':' . $throwable->getLine() . "\n\n" . $throwable->getTraceAsString()) . '</pre>'
            : '';

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>'
            . $this->escape($title)
            . '</title><style>body{margin:0;font-family:system-ui,sans-serif;background:#f8fafc;color:#111827}.wrap{max-width:920px;margin:10vh auto;padding:0 24px}h1{font-size:32px;margin:0 0 12px}p{font-size:16px;color:#4b5563}pre{overflow:auto;background:#111827;color:#f9fafb;padding:16px;border-radius:6px}</style></head><body><main class="wrap"><h1>'
            . $this->escape($title)
            . '</h1><p>'
            . $this->escape($message)
            . '</p>'
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

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
