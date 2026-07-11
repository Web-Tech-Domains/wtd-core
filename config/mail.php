<?php

declare(strict_types=1);

return [
    'default' => $_ENV['MAIL_MAILER'] ?? 'smtp',
];
