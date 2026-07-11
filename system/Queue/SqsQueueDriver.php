<?php

declare(strict_types=1);

namespace WTD\Queue;

/**
 * AWS SQS queue driver facade; SDK transport can be plugged behind the shared queue contract.
 */
final class SqsQueueDriver extends InMemoryQueueDriver
{
}
