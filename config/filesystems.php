<?php

declare(strict_types=1);

return [
    'default' => $_ENV['FILESYSTEM_DISK'] ?? 'local',
    'disks' => [
        'local' => [
            'root' => 'storage/app',
        ],
    ],
];
