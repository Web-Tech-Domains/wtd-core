<?php

declare(strict_types=1);

$environment = env('APP_ENV', 'development');

return [
    'name' => env('APP_NAME', 'WTD Core'),
    'env' => $environment,
    'debug' => env('APP_DEBUG', $environment !== 'production'),
    'key' => env('APP_KEY', 'wtd-core'),
    'url' => env('APP_URL', 'http://localhost'),
    'providers' => [],
];
