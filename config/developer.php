<?php

declare(strict_types=1);

return [
    'enabled' => env('APP_ENV', 'production') !== 'production',
    'debug_toolbar' => env('WTD_DEBUG_TOOLBAR', false),
    'api_docs' => env('WTD_API_DOCS', false),
    'error_pages' => true,
    'benchmark_iterations' => 100,
];
