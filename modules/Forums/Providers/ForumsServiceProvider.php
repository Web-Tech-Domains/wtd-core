<?php

declare(strict_types=1);

namespace Modules\Forums\Providers;

use WTD\Support\ServiceProvider;

final class ForumsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register forum services, repositories, or policies here as the module grows.
    }

    public function boot(): void
    {
        // Boot forum integrations here.
    }
}
