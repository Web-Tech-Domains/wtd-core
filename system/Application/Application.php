<?php

declare(strict_types=1);

namespace WTD\Application;

use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * Coordinates the core framework lifecycle and shared services.
 */
final class Application
{
    public const VERSION = '0.1.0-alpha';

    /**
     * @var list<ServiceProvider>
     */
    private array $providers = [];

    private bool $booted = false;

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

    /**
     * Register a service provider with the application.
     */
    public function register(ServiceProvider|string $provider): ServiceProvider
    {
        $instance = is_string($provider) ? $this->container->get($provider) : $provider;

        if (!$instance instanceof ServiceProvider) {
            throw new InvalidArgumentException('Service provider must extend ' . ServiceProvider::class . '.');
        }

        $instance->register();
        $this->providers[] = $instance;

        if ($this->booted) {
            $instance->boot();
        }

        return $instance;
    }

    /**
     * Boot all registered service providers once.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }

    /**
     * Determine whether the application has completed provider booting.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Return registered service providers.
     *
     * @return list<ServiceProvider>
     */
    public function providers(): array
    {
        return $this->providers;
    }
}
