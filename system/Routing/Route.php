<?php

declare(strict_types=1);

namespace WTD\Routing;

use Closure;
use WTD\Http\Request;
use WTD\Http\Response;

/**
 * Defines an HTTP route.
 */
final class Route
{
    /**
     * @var array<string, string>
     */
    private array $parameters = [];

    /**
     * @param \Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly Closure|array|string $action,
        private ?string $name = null,
    ) {
    }

    /**
     * Return the route HTTP method.
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Return the route path pattern.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Set the route name.
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Return the route name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Return the route action.
     *
     * @return Closure|class-string|array{0: class-string, 1: non-empty-string}
     */
    public function action(): Closure|array|string
    {
        return $this->action;
    }

    /**
     * Build a URI for this route.
     *
     * @param array<string, string|int> $parameters
     */
    public function uri(array $parameters = []): string
    {
        $uri = preg_replace_callback(
            '/\{([A-Za-z_][A-Za-z0-9_]*)}/',
            static fn (array $matches): string => rawurlencode((string) ($parameters[$matches[1]] ?? '')),
            $this->path,
        );

        return $this->normalize($uri ?? $this->path);
    }

    /**
     * Determine whether the route matches a request method and path.
     */
    public function matches(string $method, string $path): bool
    {
        if ($this->method !== strtoupper($method)) {
            return false;
        }

        $parameterNames = [];
        $pattern = preg_replace_callback('/\{([A-Za-z_][A-Za-z0-9_]*)}/', static function (array $matches) use (&$parameterNames): string {
            $parameterNames[] = $matches[1];

            return '([^/]+)';
        }, $this->normalize($this->path));

        if ($pattern === null) {
            return false;
        }

        if (preg_match('#^' . $pattern . '$#', $this->normalize($path), $matches) !== 1) {
            return false;
        }

        array_shift($matches);
        $this->parameters = array_combine($parameterNames, $matches) ?: [];

        return true;
    }

    /**
     * Run the route action.
     */
    public function run(Request $request, ControllerDispatcher $dispatcher): Response
    {
        return $dispatcher->dispatch($this->action, $request, $this->parameters);
    }

    /**
     * Normalize a path for matching.
     */
    private function normalize(string $path): string
    {
        return '/' . trim($path, '/');
    }
}
