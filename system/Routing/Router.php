<?php

declare(strict_types=1);

namespace WTD\Routing;

use Closure;
use WTD\Exception\NotFoundHttpException;
use WTD\Http\Request;
use WTD\Http\Response;

/**
 * Stores and resolves HTTP routes.
 */
final class Router
{
    /**
     * @var list<Route>
     */
    private array $routes = [];

    /**
     * @var list<string>
     */
    private array $groupPrefixes = [];

    public function __construct(private readonly ControllerDispatcher $dispatcher)
    {
    }

    /**
     * Register a GET route.
     *
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function get(string $path, Closure|array|string $action): Route
    {
        return $this->add('GET', $path, $action);
    }

    /**
     * Register a POST route.
     *
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function post(string $path, Closure|array|string $action): Route
    {
        return $this->add('POST', $path, $action);
    }

    /**
     * Register a route from cached metadata.
     *
     * @param class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function addCached(string $method, string $path, string|array $action, ?string $name = null): Route
    {
        $route = new Route($method, $path, $action, $name);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Replace all registered routes.
     */
    public function clear(): void
    {
        $this->routes = [];
    }

    /**
     * Register routes with a shared path prefix.
     *
     * @param Closure(self): void $routes
     */
    public function group(string $prefix, Closure $routes): void
    {
        $this->groupPrefixes[] = $prefix;
        $routes($this);
        array_pop($this->groupPrefixes);
    }

    /**
     * Resolve a matching route.
     */
    public function resolve(Request $request): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request->method(), $request->path())) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Dispatch a request to a route.
     */
    public function dispatch(Request $request): Response
    {
        $route = $this->resolve($request);

        if ($route === null) {
            throw new NotFoundHttpException();
        }

        return $route->run($request, $this->dispatcher);
    }

    /**
     * Return registered routes.
     *
     * @return list<Route>
     */
    public function routes(): array
    {
        return $this->routes;
    }

    /**
     * Find a route by name.
     */
    public function route(string $name): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    private function add(string $method, string $path, Closure|array|string $action): Route
    {
        $route = new Route($method, $this->prefix($path), $action);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Apply active group prefixes to a path.
     */
    private function prefix(string $path): string
    {
        $prefix = implode('/', array_map(
            static fn (string $part): string => trim($part, '/'),
            $this->groupPrefixes,
        ));

        return '/' . trim($prefix . '/' . trim($path, '/'), '/');
    }
}
