<?php

declare(strict_types=1);

namespace WTD\Routing;

use Closure;
use RuntimeException;
use WTD\Filesystem\Filesystem;

/**
 * Reads and writes cached route metadata.
 */
final class RouteCache
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $path,
    ) {
    }

    /**
     * Determine whether cached routes exist.
     */
    public function exists(): bool
    {
        return $this->filesystem->exists($this->path);
    }

    /**
     * Write route metadata to cache.
     */
    public function write(Router $router): void
    {
        $routes = [];

        foreach ($router->routes() as $route) {
            $action = $route->action();

            if ($action instanceof Closure) {
                throw new RuntimeException(sprintf(
                    'Route [%s] cannot be cached because it uses a closure action.',
                    $route->path(),
                ));
            }

            $routes[] = [
                'method' => $route->method(),
                'path' => $route->path(),
                'action' => $action,
                'name' => $route->getName(),
                'domain' => $route->getDomain(),
                'middleware' => $route->getMiddleware(),
            ];
        }

        $this->filesystem->put($this->path, "<?php\n\nreturn " . var_export($routes, true) . ";\n");
    }

    /**
     * Load cached route metadata into the router.
     */
    public function load(Router $router): void
    {
        if (!$this->exists()) {
            return;
        }

        $routes = require $this->path;

        if (!is_array($routes)) {
            throw new RuntimeException(sprintf('Route cache [%s] must return an array.', $this->path));
        }

        $router->clear();

        foreach ($routes as $route) {
            if (!is_array($route)) {
                continue;
            }

            /** @var array{method: string, path: string, action: class-string|array{0: class-string, 1: non-empty-string}, name?: string|null, domain?: string|null, middleware?: list<class-string<\WTD\Middleware\Middleware>>} $route */
            $router->addCached(
                $route['method'],
                $route['path'],
                $route['action'],
                $route['name'] ?? null,
                $route['domain'] ?? null,
                $route['middleware'] ?? [],
            );
        }
    }

    /**
     * Remove cached routes.
     */
    public function clear(): void
    {
        $this->filesystem->delete($this->path);
    }
}
