<?php

declare(strict_types=1);

namespace WTD\Events;

interface EventSubscriber
{
    public function subscribe(Dispatcher $events): void;
}
