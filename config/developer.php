<?php

declare(strict_types=1);

return [
    'enabled' => ($_ENV['APP_ENV'] ?? 'production') !== 'production',
    'debug_toolbar' => ($_ENV['WTD_DEBUG_TOOLBAR'] ?? 'false') === 'true',
    'api_docs' => ($_ENV['WTD_API_DOCS'] ?? 'false') === 'true',
    'error_pages' => true,
    'benchmark_iterations' => 100,
];

