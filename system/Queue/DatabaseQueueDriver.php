<?php

declare(strict_types=1);

namespace WTD\Queue;

/**
 * Database queue driver facade; storage-backed persistence can replace the inherited local store.
 */
final class DatabaseQueueDriver extends InMemoryQueueDriver
{
}
