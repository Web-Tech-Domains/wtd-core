<?php

declare(strict_types=1);

namespace WTD\Middleware;

use InvalidArgumentException;
use WTD\Container\Container;

/**
 * Resolves configured middleware classes through the container.
 */
final class MiddlewareResolver
{
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * Resolve middleware class names into instances.
     *
     * @param list<class-string<Middleware>> $middleware
     *
     * @return list<Middleware>
     */
    public function resolve(array $middleware): array
    {
        $resolved = [];

        foreach ($middleware as $class) {
            $instance = $this->container->get($class);

            if (!$instance instanceof Middleware) {
                throw new InvalidArgumentException(sprintf('Middleware [%s] must implement %s.', $class, Middleware::class));
            }

            $resolved[] = $instance;
        }

        return $resolved;
    }
}
