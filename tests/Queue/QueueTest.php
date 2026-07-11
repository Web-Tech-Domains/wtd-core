<?php

declare(strict_types=1);

namespace Tests\Queue;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Queue\BatchRepository;
use WTD\Queue\DatabaseQueueDriver;
use WTD\Queue\FailedJobProvider;
use WTD\Queue\InMemoryQueueDriver;
use WTD\Queue\Job;
use WTD\Queue\QueueDriver;
use WTD\Queue\QueueManager;
use WTD\Queue\QueueServiceProvider;
use WTD\Queue\RabbitMqQueueDriver;
use WTD\Queue\RedisQueueDriver;
use WTD\Queue\SqsQueueDriver;
use WTD\Queue\Worker;

final class QueueTest extends TestCase
{
    public function testQueuePopsHighestPriorityAvailableJob(): void
    {
        $driver = new InMemoryQueueDriver();
        $handled = [];
        $driver->push(new RecordingJob('low', $handled), priority: 1);
        $driver->push(new RecordingJob('high', $handled), priority: 10);

        $job = $driver->pop();
        self::assertNotNull($job);
        $job->job->handle();

        self::assertSame(['high'], $handled);
    }

    public function testWorkerRunsJobsAndTracksBatchSuccess(): void
    {
        $driver = new InMemoryQueueDriver();
        $failed = new FailedJobProvider();
        $batches = new BatchRepository();
        $handled = [];
        $batch = $batches->create([
            new RecordingJob('one', $handled),
            new RecordingJob('two', $handled),
        ], $driver);
        $worker = new Worker($driver, $failed, $batches);

        self::assertTrue($worker->runNext());
        self::assertTrue($worker->runNext());
        self::assertSame(['one', 'two'], $handled);
        self::assertTrue($batch->finished());
        self::assertSame(0, $batch->failedJobs);
    }

    public function testWorkerRetriesThenRecordsFailedJobs(): void
    {
        $driver = new InMemoryQueueDriver();
        $failed = new FailedJobProvider();
        $batches = new BatchRepository();
        $driver->push(new FailingJob());
        $worker = new Worker($driver, $failed, $batches, maxAttempts: 2);

        self::assertFalse($worker->runNext());
        self::assertSame(1, $driver->size());
        self::assertFalse($worker->runNext());
        self::assertCount(1, $failed->all());

        $id = $failed->all()[0]->job->id;
        self::assertTrue($failed->retry($id, $driver));
        self::assertSame(1, $driver->size());
        self::assertSame([], $failed->all());
    }

    public function testQueueManagerProvidesNamedDrivers(): void
    {
        $manager = new QueueManager('database');

        self::assertInstanceOf(DatabaseQueueDriver::class, $manager->connection());
        self::assertInstanceOf(RedisQueueDriver::class, $manager->connection('redis'));
        self::assertInstanceOf(RabbitMqQueueDriver::class, $manager->connection('rabbitmq'));
        self::assertInstanceOf(SqsQueueDriver::class, $manager->connection('sqs'));
    }

    public function testQueueServiceProviderRegistersQueueServices(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application($basePath, new Container(), new Repository(['queue.default' => 'redis']));
        $app->register(QueueServiceProvider::class);

        self::assertInstanceOf(QueueManager::class, $app->container()->get(QueueManager::class));
        self::assertInstanceOf(QueueDriver::class, $app->container()->get(QueueDriver::class));
        self::assertInstanceOf(Worker::class, $app->container()->get(Worker::class));
        self::assertInstanceOf(RedisQueueDriver::class, $app->container()->get(QueueDriver::class));
    }
}

final class RecordingJob implements Job
{
    /**
     * @param list<string> $handled
     */
    public function __construct(private readonly string $name, private array &$handled)
    {
    }

    public function handle(): void
    {
        $this->handled[] = $this->name;
    }

    /**
     * @return list<string>
     */
    public function handled(): array
    {
        return $this->handled;
    }
}

final class FailingJob implements Job
{
    public function handle(): void
    {
        throw new RuntimeException('Queue failure');
    }
}
