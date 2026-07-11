<?php

declare(strict_types=1);

use WTD\Application\Application;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;

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
