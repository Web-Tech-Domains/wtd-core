<?php

declare(strict_types=1);

use WTD\Session\StartSession;
use WTD\DeveloperExperience\DebugToolbarMiddleware;
use WTD\Tenancy\TenantMiddleware;

return [
    'middleware' => [
        StartSession::class,
        TenantMiddleware::class,
        DebugToolbarMiddleware::class,
    ],
];
