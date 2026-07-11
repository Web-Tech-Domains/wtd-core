<?php

declare(strict_types=1);

use WTD\Session\StartSession;
use WTD\DeveloperExperience\DebugToolbarMiddleware;
use WTD\Security\SecurityHeadersMiddleware;
use WTD\Tenancy\TenantMiddleware;

return [
    'middleware' => [
        StartSession::class,
        TenantMiddleware::class,
        SecurityHeadersMiddleware::class,
        DebugToolbarMiddleware::class,
    ],
];
