<?php

declare(strict_types=1);

return [
    'default' => $_ENV['QUEUE_CONNECTION'] ?? 'database',
];
