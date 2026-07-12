<?php

declare(strict_types=1);

namespace WTD\Http;

use WTD\Exception\ExceptionRenderer;
use WTD\Http\Client\HttpClient;
use WTD\Kernel\HttpKernel;
use WTD\Middleware\MiddlewareResolver;
use WTD\Middleware\Pipeline;
use WTD\Routing\ControllerDispatcher;
use WTD\Routing\RouteCache;
use WTD\Routing\Router;
use WTD\Routing\UrlGenerator;
use WTD\Session\SessionStore;
use WTD\Support\ServiceProvider;

/**
 * Registers HTTP engine services.
 */
final class HttpServiceProvider extends ServiceProvider
{
    /**
     * Register HTTP services.
     */
    public function register(): void
    {
        $this->container()->singleton(HttpClient::class);
        $this->container()->singleton(ControllerDispatcher::class);
        $this->container()->singleton(
            RouteCache::class,
            fn (): RouteCache => new RouteCache(
                $this->container()->get(\WTD\Filesystem\Filesystem::class),
                $this->app->basePath('storage/framework/routes.php'),
            ),
        );
        $this->container()->singleton(
            SessionStore::class,
            fn (): SessionStore => new SessionStore(
                $this->container()->get(\WTD\Filesystem\Filesystem::class),
                $this->app->basePath('storage/sessions'),
            ),
        );
        $this->container()->singleton(Pipeline::class);
        $this->container()->singleton(ExceptionRenderer::class);
        $this->container()->singleton(MiddlewareResolver::class);
        $this->container()->singleton(
            Router::class,
            fn (): Router => new Router(
                $this->container()->get(ControllerDispatcher::class),
                $this->container()->get(MiddlewareResolver::class),
                $this->container()->get(Pipeline::class),
            ),
        );
        $this->container()->singleton(UrlGenerator::class);
        $this->container()->singleton(HttpKernel::class, function (): HttpKernel {
            $configured = $this->app->config()->get('http.middleware', []);
            $middleware = [];

            if (is_array($configured)) {
                foreach ($configured as $candidate) {
                    if (
                        is_string($candidate)
                        && is_a($candidate, \WTD\Middleware\Middleware::class, true)
                    ) {
                        $middleware[] = $candidate;
                    }
                }
            }

            return new HttpKernel(
                $this->container()->get(Router::class),
                $this->container()->get(Pipeline::class),
                $this->container()->get(ExceptionRenderer::class),
                $this->container()->get(MiddlewareResolver::class)->resolve($middleware),
            );
        });
    }

    /**
     * Load route files after providers have registered.
     */
    public function boot(): void
    {
        /** @var Router $router */
        $router = $this->container()->get(Router::class);
        /** @var RouteCache $cache */
        $cache = $this->container()->get(RouteCache::class);

        if ($cache->exists()) {
            $cache->load($router);
            return;
        }

        $routes = $this->app->basePath('routes/web.php');

        if (is_file($routes)) {
            require $routes;
        }
    }
}
