<?php

declare(strict_types=1);

return [
    'default' => $_ENV['CACHE_STORE'] ?? 'file',
];
