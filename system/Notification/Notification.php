<?php

declare(strict_types=1);

namespace WTD\Notification;

interface Notification
{
    /**
     * @return list<string>
     */
    public function via(Notifiable $notifiable): array;
}
