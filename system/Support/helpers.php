<?php

declare(strict_types=1);

use WTD\Application\Application;
use WTD\Cache\CacheManager;
use WTD\Cache\CacheRepository;
use WTD\Cookie\Cookie;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Hooks\HookManager;
use WTD\Http\Client\HttpClient;
use WTD\Session\SessionStore;
use WTD\View\AssetManager;
use WTD\View\ViewRenderer;

if (!function_exists('app')) {
    function app(?string $abstract = null): mixed
    {
        if (isset($GLOBALS['wtd_app']) && $GLOBALS['wtd_app'] instanceof Application) {
            return $abstract === null ? $GLOBALS['wtd_app'] : $GLOBALS['wtd_app']->container()->get($abstract);
        }

        /** @var Application $application */
        $application = require dirname(__DIR__, 2) . '/bootstrap/app.php';

        return $abstract === null ? $application : $application->container()->get($abstract);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        if (!is_string($value)) {
            return $value;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path === '' ? '' : DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR));
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path === '' ? '' : DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR)));
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path === '' ? '' : DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR)));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path === '' ? '' : DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR)));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path === '' ? '' : DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR)));
    }
}

if (!function_exists('db')) {
    function db(?string $connection = null): Connection
    {
        return app(DatabaseManager::class)->connection($connection);
    }
}

if (!function_exists('http')) {
    function http(): HttpClient
    {
        return app(HttpClient::class);
    }
}

if (!function_exists('session')) {
    /**
     * @param array<string, mixed>|string|null $key
     */
    function session(array|string|null $key = null, mixed $default = null): mixed
    {
        $session = app(SessionStore::class);

        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $session->put($name, $value);
            }

            return null;
        }

        return $key === null ? $session : $session->get($key, $default);
    }
}

if (!function_exists('cache')) {
    /**
     * @param array<string, mixed>|string|null $key
     */
    function cache(array|string|null $key = null, mixed $default = null, ?int $ttlSeconds = null): mixed
    {
        $cache = app(CacheRepository::class);

        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $cache->put($name, $value, $ttlSeconds);
            }

            return null;
        }

        return $key === null ? $cache : $cache->get($key, $default);
    }
}

if (!function_exists('cache_store')) {
    function cache_store(?string $store = null): CacheRepository
    {
        return app(CacheManager::class)->store($store);
    }
}

if (!function_exists('cookie')) {
    function cookie(
        string $name,
        string $value,
        int $minutes = 0,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax',
    ): Cookie {
        return new Cookie(
            $name,
            $value,
            $minutes > 0 ? time() + ($minutes * 60) : 0,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite,
        );
    }
}

if (!function_exists('forget_cookie')) {
    function forget_cookie(
        string $name,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax',
    ): Cookie {
        return new Cookie($name, '', time() - 3600, $path, $domain, $secure, $httpOnly, $sameSite);
    }
}

if (!function_exists('view')) {
    /**
     * @param array<string, mixed> $data
     */
    function view(string $view, array $data = []): string
    {
        return app(ViewRenderer::class)->render($view, $data);
    }
}

if (!function_exists('vite')) {
    /**
     * @param string|list<string> $entries
     */
    function vite(string|array $entries): string
    {
        return app(AssetManager::class)->tags(is_string($entries) ? [$entries] : $entries);
    }
}

if (!function_exists('module_view')) {
    /**
     * @param array<string, mixed> $data
     */
    function module_view(string $module, string $view, array $data = []): string
    {
        return app(ViewRenderer::class)->renderModule($module, $view, $data);
    }
}

if (!function_exists('app_hooks')) {
    function app_hooks(): HookManager
    {
        return app(HookManager::class);
    }
}

if (!function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10): void
    {
        app_hooks()->addAction($hook, $callback, $priority);
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hook, mixed ...$payload): void
    {
        app_hooks()->doAction($hook, ...$payload);
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10): void
    {
        app_hooks()->addFilter($hook, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value, mixed ...$payload): mixed
    {
        return app_hooks()->applyFilters($hook, $value, ...$payload);
    }
}

if (!function_exists('register_data_insert_hook')) {
    function register_data_insert_hook(callable $callback, int $priority = 10): void
    {
        add_action('data.inserted', $callback, $priority);
    }
}

if (!function_exists('register_data_update_hook')) {
    function register_data_update_hook(callable $callback, int $priority = 10): void
    {
        add_action('data.updated', $callback, $priority);
    }
}

if (!function_exists('register_data_delete_hook')) {
    function register_data_delete_hook(callable $callback, int $priority = 10): void
    {
        add_action('data.deleted', $callback, $priority);
    }
}
