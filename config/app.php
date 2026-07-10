<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'WTD Core',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
];
