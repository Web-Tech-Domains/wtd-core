<?php

declare(strict_types=1);

namespace WTD\WebSocket;

use WTD\Support\ServiceProvider;

final class WebSocketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(WebSocketHandshake::class);
        $this->container()->singleton(WebSocketFrame::class);
        $this->container()->singleton(WebSocketChannelManager::class);
    }
}
