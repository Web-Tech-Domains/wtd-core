<?php

declare(strict_types=1);

return [
    'jwt_secret' => env('AUTH_JWT_SECRET', env('APP_KEY', 'wtd-core')),
];
