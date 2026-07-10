<?php

declare(strict_types=1);

use WTD\Session\StartSession;

return [
    'middleware' => [
        StartSession::class,
    ],
];
