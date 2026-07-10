<?php

declare(strict_types=1);

namespace WTD\Routing;

use Closure;
use WTD\Exception\MethodNotAllowedHttpException;
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

    /**
     * @var list<string>
     */
    private array $groupDomains = [];

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
     * Register a PUT route.
     *
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function put(string $path, Closure|array|string $action): Route
    {
        return $this->add('PUT', $path, $action);
    }

    /**
     * Register a PATCH route.
     *
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function patch(string $path, Closure|array|string $action): Route
    {
        return $this->add('PATCH', $path, $action);
    }

    /**
     * Register a DELETE route.
     *
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function delete(string $path, Closure|array|string $action): Route
    {
        return $this->add('DELETE', $path, $action);
    }

    /**
     * Register an OPTIONS route.
     *
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function options(string $path, Closure|array|string $action): Route
    {
        return $this->add('OPTIONS', $path, $action);
    }

    /**
     * Register a route from cached metadata.
     *
     * @param class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function addCached(string $method, string $path, string|array $action, ?string $name = null, ?string $domain = null): Route
    {
        $route = new Route($method, $path, $action, $name, $domain);
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
     * Register routes with a shared domain constraint.
     *
     * @param Closure(self): void $routes
     */
    public function domain(string $domain, Closure $routes): void
    {
        $this->groupDomains[] = strtolower($domain);
        $routes($this);
        array_pop($this->groupDomains);
    }

    /**
     * Register versioned API routes under /api/v{version}.
     *
     * @param Closure(self): void $routes
     */
    public function version(string $version, Closure $routes): void
    {
        $this->group('/api/v' . trim($version, 'vV/'), $routes);
    }

    /**
     * Resolve a matching route.
     */
    public function resolve(Request $request): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {
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
        if (strtoupper($request->method()) === 'OPTIONS') {
            $allowedMethods = $this->allowedMethods($request);

            if ($allowedMethods !== []) {
                return Response::make('', 204)->withHeader('Allow', implode(', ', $this->normalizeAllowedMethods($allowedMethods)));
            }
        }

        $route = $this->resolve($request);

        if ($route === null) {
            $allowedMethods = $this->allowedMethods($request);

            if ($allowedMethods !== []) {
                throw new MethodNotAllowedHttpException($this->normalizeAllowedMethods($allowedMethods));
            }

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
     * Return allowed methods for a request path and domain.
     *
     * @return list<string>
     */
    public function allowedMethods(Request $request): array
    {
        $methods = [];

        foreach ($this->routes as $route) {
            if ($route->matchesPathAndDomain($request)) {
                $methods[] = $route->method();
            }
        }

        return array_values(array_unique($methods));
    }

    /**
     * Include implicit HEAD and OPTIONS support in allowed methods.
     *
     * @param list<string> $methods
     *
     * @return list<string>
     */
    private function normalizeAllowedMethods(array $methods): array
    {
        if (in_array('GET', $methods, true) && !in_array('HEAD', $methods, true)) {
            $methods[] = 'HEAD';
        }

        if (!in_array('OPTIONS', $methods, true)) {
            $methods[] = 'OPTIONS';
        }

        sort($methods);

        return array_values($methods);
    }

    /**
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    private function add(string $method, string $path, Closure|array|string $action): Route
    {
        $route = new Route($method, $this->prefix($path), $action, null, $this->domainConstraint());
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

    /**
     * Return the active domain constraint.
     */
    private function domainConstraint(): ?string
    {
        return $this->groupDomains[count($this->groupDomains) - 1] ?? null;
    }
}
