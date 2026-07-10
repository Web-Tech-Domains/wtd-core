<?php

declare(strict_types=1);

namespace WTD\Application;

use WTD\Config\Repository;
use WTD\Container\Container;

/**
 * Coordinates the core framework lifecycle and shared services.
 */
final class Application
{
    public const VERSION = '0.1.0-alpha';

    /**
     * @param non-empty-string $basePath
     */
    public function __construct(
        private readonly string $basePath,
        private readonly Container $container,
        private readonly Repository $config,
    ) {
        $this->container->instance(self::class, $this);
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(Repository::class, $this->config);
    }

    /**
     * Return the absolute project root path.
     */
    public function basePath(string $path = ''): string
    {
        return $path === ''
            ? $this->basePath
            : $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Return the configured application name.
     */
    public function name(): string
    {
        return (string) $this->config->get('app.name', 'WTD Core');
    }

    /**
     * Return the framework version.
     */
    public function version(): string
    {
        return self::VERSION;
    }

    /**
     * Return the service container.
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Return the configuration repository.
     */
    public function config(): Repository
    {
        return $this->config;
    }
}
