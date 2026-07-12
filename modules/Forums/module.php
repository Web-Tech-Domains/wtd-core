<?php

declare(strict_types=1);

return [
    'name' => 'Forums',
    'slug' => 'forums',
    'providers' => [
        Modules\Forums\Providers\ForumsServiceProvider::class,
    ],
    'routes' => 'modules/Forums/Routes/web.php',
];

