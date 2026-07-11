<?php

declare(strict_types=1);

namespace WTD\Events;

use ReflectionClass;
use ReflectionNamedType;

final class EventDiscovery
{
    /**
     * @param list<class-string> $listeners
     */
    public function discover(array $listeners, Dispatcher $dispatcher): void
    {
        foreach ($listeners as $listenerClass) {
            $reflection = new ReflectionClass($listenerClass);

            if (!$reflection->hasMethod('handle')) {
                continue;
            }

            $parameter = $reflection->getMethod('handle')->getParameters()[0] ?? null;
            $type = $parameter?->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            /** @var class-string $eventClass */
            $eventClass = $type->getName();
            $dispatcher->listen($eventClass, $reflection->newInstance());
        }
    }
}
