<?php

declare(strict_types=1);

namespace WTD\DeveloperExperience;

use WTD\Application\Application;
use WTD\Routing\Route;
use WTD\Routing\Router;

/**
 * Generates a minimal OpenAPI document from registered routes.
 */
final class OpenApiGenerator
{
    public function __construct(
        private readonly Application $app,
        private readonly Router $router,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function generate(): array
    {
        $paths = [];

        foreach ($this->router->routes() as $route) {
            $paths[$route->path()][strtolower($route->method())] = $this->operation($route);
        }

        ksort($paths);

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => $this->app->name(),
                'version' => $this->app->version(),
            ],
            'paths' => $paths,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function operation(Route $route): array
    {
        return [
            'operationId' => $route->getName() ?? $this->operationId($route),
            'summary' => $route->getName() ?? $route->method() . ' ' . $route->path(),
            'parameters' => $this->parameters($route->path()),
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                ],
            ],
        ];
    }

    private function operationId(Route $route): string
    {
        $id = strtolower($route->method()) . '_' . trim(str_replace(['/', '{', '}'], '_', $route->path()), '_');
        $id = preg_replace('/_+/', '_', $id) ?? $id;

        return $id;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parameters(string $path): array
    {
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)}/', $path, $matches);

        return array_map(static fn (string $name): array => [
            'name' => $name,
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string'],
        ], $matches[1]);
    }
}
