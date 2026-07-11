<?php

declare(strict_types=1);

namespace WTD\Notification;

interface Notifiable
{
    public function routeNotificationFor(string $channel): mixed;
}
