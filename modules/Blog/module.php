<?php

declare(strict_types=1);

return [
    'name' => 'Blog',
    'slug' => 'blog',
    'providers' => [
        Modules\Blog\Providers\BlogServiceProvider::class,
    ],
    'routes' => 'modules/Blog/Routes/web.php',
];
