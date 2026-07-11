<?php

declare(strict_types=1);

use WTD\Session\StartSession;
use WTD\DeveloperExperience\DebugToolbarMiddleware;

return [
    'middleware' => [
        StartSession::class,
        DebugToolbarMiddleware::class,
    ],
];
