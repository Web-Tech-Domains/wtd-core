<?php

declare(strict_types=1);

namespace WTD\Events;

interface StoppableEvent
{
    public function isPropagationStopped(): bool;
}
