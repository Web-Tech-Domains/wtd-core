<?php

declare(strict_types=1);

namespace WTD\Routing;

use Closure;
use RuntimeException;
use WTD\Exception\MethodNotAllowedHttpException;
use WTD\Exception\NotFoundHttpException;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;
use WTD\Middleware\MiddlewareResolver;
use WTD\Middleware\Pipeline;

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

    public function __construct(
        private readonly ControllerDispatcher $dispatcher,
        private readonly ?MiddlewareResolver $middlewareResolver = null,
        private readonly ?Pipeline $pipeline = null,
    ) {
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
     * @param list<class-string<Middleware>> $middleware
     */
    public function addCached(
        string $method,
        string $path,
        string|array $action,
        ?string $name = null,
        ?string $domain = null,
        array $middleware = [],
    ): Route {
        $route = new Route($method, $path, $action, $name, $domain, $middleware);
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

        return $this->runRoute($route, $request);
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
     * Execute route-specific middleware before the route action.
     */
    private function runRoute(Route $route, Request $request): Response
    {
        $middleware = $route->getMiddleware();

        if ($middleware === []) {
            return $route->run($request, $this->dispatcher);
        }

        if ($this->middlewareResolver === null || $this->pipeline === null) {
            throw new RuntimeException('Route middleware requires a middleware resolver and pipeline.');
        }

        return $this->pipeline->handle(
            $request,
            $this->middlewareResolver->resolve($middleware),
            fn (Request $request): Response => $route->run($request, $this->dispatcher),
        );
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
