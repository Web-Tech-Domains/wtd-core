<?php

declare(strict_types=1);

namespace WTD\DeveloperExperience;

use WTD\Routing\Router;

/**
 * Renders a small HTML API documentation page from route metadata.
 */
final class ApiDocumentationRenderer
{
    public function __construct(private readonly Router $router)
    {
    }

    public function render(string $openApiUrl = '/docs/openapi.json'): string
    {
        $rows = '';

        foreach ($this->router->routes() as $route) {
            $rows .= '<tr><td><code>' . $this->escape($route->method()) . '</code></td><td><code>'
                . $this->escape($route->path())
                . '</code></td><td>'
                . $this->escape($route->getName() ?? '')
                . '</td></tr>';
        }

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>API Documentation</title><link rel="icon" href="/favicon.svg" type="image/svg+xml"><link rel="manifest" href="/site.webmanifest"><style>body{font-family:system-ui,sans-serif;margin:40px;color:#111827}.brand{display:flex;align-items:center;gap:10px;margin-bottom:24px;font-weight:800;color:#172554}.brand img{width:34px;height:34px}table{border-collapse:collapse;width:100%}th,td{border-bottom:1px solid #e5e7eb;padding:10px;text-align:left}code{font-family:ui-monospace,monospace}</style></head><body><div class="brand"><img src="/favicon.svg" alt="WTD Core"><span>WTD Core</span></div><h1>API Documentation</h1><p><a href="'
            . $this->escape($openApiUrl)
            . '">OpenAPI JSON</a></p><table><thead><tr><th>Method</th><th>Path</th><th>Name</th></tr></thead><tbody>'
            . $rows
            . '</tbody></table></body></html>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
