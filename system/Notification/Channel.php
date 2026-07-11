<?php

declare(strict_types=1);

namespace WTD\Notification;

interface Channel
{
    public function send(Notifiable $notifiable, Notification $notification): void;
}
