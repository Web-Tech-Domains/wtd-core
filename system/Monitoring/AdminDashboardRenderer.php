<?php

declare(strict_types=1);

namespace WTD\Monitoring;

final class AdminDashboardRenderer
{
    /**
     * @param array<string, mixed> $report
     */
    public function render(array $report): string
    {
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>WTD Admin</title></head><body><h1>WTD Admin</h1><pre>'
            . htmlspecialchars(json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8')
            . '</pre></body></html>';
    }
}
