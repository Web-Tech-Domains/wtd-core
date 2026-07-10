<?php

declare(strict_types=1);

namespace WTD\Support;

use WTD\Application\Application;
use WTD\Container\Container;

/**
 * Base class for framework and package service registration.
 */
abstract class ServiceProvider
{
    public function __construct(protected readonly Application $app)
    {
    }

    /**
     * Register container bindings.
     */
    public function register(): void
    {
    }

    /**
     * Run logic after all providers have been registered.
     */
    public function boot(): void
    {
    }

    /**
     * Return the application container.
     */
    protected function container(): Container
    {
        return $this->app->container();
    }
}
