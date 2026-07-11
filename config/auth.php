<?php

declare(strict_types=1);

return [
    'jwt_secret' => $_ENV['AUTH_JWT_SECRET'] ?? $_ENV['APP_KEY'] ?? 'wtd-core',
];
