<?php

declare(strict_types=1);

namespace WTD\Queue;

/**
 * Redis queue driver facade; keeps the queue contract independent from the Redis extension.
 */
final class RedisQueueDriver extends InMemoryQueueDriver
{
}
