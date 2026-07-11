<?php

declare(strict_types=1);

return [
    /*
     * Automatically load modules that expose modules/<Name>/module.php.
     */
    'auto_discover' => true,

    /*
     * Each enabled module may declare service providers and an optional route file.
     *
     * Example:
     * [
     *     'name' => 'Blog',
     *     'providers' => [Modules\Blog\BlogServiceProvider::class],
     *     'routes' => 'modules/Blog/routes/web.php',
     * ]
     */
    'enabled' => [],
];
