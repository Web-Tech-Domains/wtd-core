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

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>API Documentation</title><style>body{font-family:system-ui,sans-serif;margin:40px;color:#111827}table{border-collapse:collapse;width:100%}th,td{border-bottom:1px solid #e5e7eb;padding:10px;text-align:left}code{font-family:ui-monospace,monospace}</style></head><body><h1>API Documentation</h1><p><a href="'
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
