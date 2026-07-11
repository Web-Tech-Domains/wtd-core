<?php

declare(strict_types=1);

namespace WTD\Modules;

use WTD\Routing\Router;
use WTD\Support\ServiceProvider;

/**
 * Loads application modules, third-party module providers, and module routes.
 */
final class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach ($this->modules() as $module) {
            $this->registerProviders($module['providers'] ?? []);
            $this->loadRoutes($module['routes'] ?? null);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function modules(): array
    {
        $modules = $this->app->config()->get('modules.enabled', []);
        $configured = is_array($modules) ? array_values(array_filter($modules, 'is_array')) : [];

        if (!(bool) $this->app->config()->get('modules.auto_discover', true)) {
            return $configured;
        }

        return array_merge($configured, $this->discoverModules());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function discoverModules(): array
    {
        $files = glob($this->app->basePath('modules/*/module.php'));

        if ($files === false) {
            return [];
        }

        sort($files);
        $modules = [];

        foreach ($files as $file) {
            $module = require $file;

            if (is_array($module)) {
                /** @var array<string, mixed> $module */
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * @param mixed $providers
     */
    private function registerProviders(mixed $providers): void
    {
        if (!is_array($providers)) {
            return;
        }

        foreach ($providers as $provider) {
            if (is_string($provider) && class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    private function loadRoutes(mixed $routes): void
    {
        if (!is_string($routes) || !$this->container()->has(Router::class)) {
            return;
        }

        $path = $this->app->basePath($routes);

        if (!is_file($path)) {
            return;
        }

        /** @var Router $router */
        $router = $this->container()->get(Router::class);
        require $path;
    }
}
