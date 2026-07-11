<?php

declare(strict_types=1);

namespace WTD\View;

use WTD\Support\ServiceProvider;

final class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(ViewRenderer::class);
        $this->container()->singleton(AssetManager::class);
    }
}
