<?php

declare(strict_types=1);

namespace WTD\Queue;

/**
 * RabbitMQ queue driver facade; AMQP transport can be plugged behind the shared queue contract.
 */
final class RabbitMqQueueDriver extends InMemoryQueueDriver
{
}
