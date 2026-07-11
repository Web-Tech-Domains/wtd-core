<?php

declare(strict_types=1);

namespace WTD\Queue;

use InvalidArgumentException;

final class QueueManager
{
    /**
     * @var array<string, QueueDriver>
     */
    private array $drivers = [];

    public function __construct(private readonly string $default = 'database')
    {
        $this->drivers = [
            'database' => new DatabaseQueueDriver(),
            'redis' => new RedisQueueDriver(),
            'rabbitmq' => new RabbitMqQueueDriver(),
            'sqs' => new SqsQueueDriver(),
        ];
    }

    public function connection(?string $name = null): QueueDriver
    {
        $name ??= $this->default;

        return $this->drivers[$name] ?? throw new InvalidArgumentException(sprintf('Queue driver [%s] is not configured.', $name));
    }
}
