<?php

declare(strict_types=1);

namespace WTD\Routing;

use InvalidArgumentException;

/**
 * Generates URLs for named routes.
 */
final class UrlGenerator
{
    public function __construct(private readonly Router $router)
    {
    }

    /**
     * Generate a path for a named route.
     *
     * @param array<string, string|int> $parameters
     */
    public function route(string $name, array $parameters = []): string
    {
        $route = $this->router->route($name);

        if ($route === null) {
            throw new InvalidArgumentException(sprintf('Route [%s] is not defined.', $name));
        }

        $path = $route->uri($parameters);
        $query = $this->queryParameters($route, $parameters);

        if ($query === []) {
            return $path;
        }

        return $path . '?' . http_build_query($query);
    }

    /**
     * @param array<string, string|int> $parameters
     *
     * @return array<string, string|int>
     */
    private function queryParameters(Route $route, array $parameters): array
    {
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)}/', $route->path(), $matches);
        $pathParameters = array_flip($matches[1]);

        return array_diff_key($parameters, $pathParameters);
    }
}
