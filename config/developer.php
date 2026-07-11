<?php

declare(strict_types=1);

$environment = env('APP_ENV', 'development');

return [
    'enabled' => $environment !== 'production',
    'debug_toolbar' => env('WTD_DEBUG_TOOLBAR', false),
    'api_docs' => env('WTD_API_DOCS', false),
    'error_pages' => true,
    'benchmark_iterations' => 100,
];
