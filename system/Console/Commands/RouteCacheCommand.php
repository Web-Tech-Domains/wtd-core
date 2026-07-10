<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Routing\ControllerDispatcher;
use WTD\Routing\RouteCache;
use WTD\Routing\Router;

/**
 * Builds the route cache.
 */
final class RouteCacheCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly ControllerDispatcher $dispatcher,
        private readonly RouteCache $cache,
    ) {
    }

    public function name(): string
    {
        return 'route:cache';
    }

    public function description(): string
    {
        return 'Build the route cache.';
    }

    public function handle(Input $input, Output $output): int
    {
        $router = new Router($this->dispatcher);
        $routes = $this->app->basePath('routes/web.php');

        if (is_file($routes)) {
            require $routes;
        }

        $this->cache->write($router);
        $output->line('Routes cached');

        return 0;
    }
}
