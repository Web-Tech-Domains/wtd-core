<?php

declare(strict_types=1);

namespace Tests\Events;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Events\Broadcastable;
use WTD\Events\Broadcaster;
use WTD\Events\Dispatcher;
use WTD\Events\EventDiscovery;
use WTD\Events\EventSubscriber;
use WTD\Events\EventsServiceProvider;
use WTD\Events\ShouldQueue;
use WTD\Events\StoppableEvent;
use WTD\Queue\InMemoryQueueDriver;
use WTD\Queue\QueueDriver;

final class EventsTest extends TestCase
{
    public function testDispatcherCallsListenersAndReturnsResponses(): void
    {
        $dispatcher = new Dispatcher();
        $seen = [];
        $dispatcher->listen(UserRegistered::class, static function (UserRegistered $event) use (&$seen): string {
            $seen[] = $event->email;

            return 'handled';
        });

        self::assertSame(['handled'], $dispatcher->dispatch(new UserRegistered('user@example.test')));
        self::assertSame(['user@example.test'], $seen);
    }

    public function testSubscribersRegisterListeners(): void
    {
        $dispatcher = new Dispatcher();
        $subscriber = new UserSubscriber();
        $dispatcher->subscribe($subscriber);

        $dispatcher->dispatch(new UserRegistered('subscriber@example.test'));

        self::assertSame(['subscriber@example.test'], $subscriber->seen);
    }

    public function testStoppableEventsStopFurtherListeners(): void
    {
        $dispatcher = new Dispatcher();
        $seen = [];
        $dispatcher->listen(StoppableUserRegistered::class, static function (StoppableUserRegistered $event) use (&$seen): void {
            $seen[] = 'first';
            $event->stop();
        });
        $dispatcher->listen(StoppableUserRegistered::class, static function () use (&$seen): void {
            $seen[] = 'second';
        });

        $dispatcher->dispatch(new StoppableUserRegistered('stop@example.test'));

        self::assertSame(['first'], $seen);
    }

    public function testBroadcastableEventsAreBroadcast(): void
    {
        $broadcaster = new Broadcaster();
        $dispatcher = new Dispatcher(broadcaster: $broadcaster);

        $dispatcher->dispatch(new BroadcastUserRegistered('broadcast@example.test'));

        self::assertSame([[
            'channel' => 'users',
            'payload' => ['email' => 'broadcast@example.test'],
        ]], $broadcaster->broadcasts());
    }

    public function testQueuedListenersArePushedToQueue(): void
    {
        $queue = new InMemoryQueueDriver();
        $dispatcher = new Dispatcher(queue: $queue);
        $listener = new QueuedUserListener();
        $dispatcher->listen(UserRegistered::class, $listener);

        $dispatcher->dispatch(new UserRegistered('queued@example.test'));

        self::assertSame(1, $queue->size());

        $job = $queue->pop();
        self::assertNotNull($job);
        $job->job->handle();

        self::assertSame(['queued@example.test'], $listener->seen);
    }

    public function testEventDiscoveryRegistersListenersByHandleParameter(): void
    {
        $dispatcher = new Dispatcher();
        $discovery = new EventDiscovery();
        DiscoveredUserListener::$seen = [];

        $discovery->discover([DiscoveredUserListener::class], $dispatcher);
        $dispatcher->dispatch(new UserRegistered('discovered@example.test'));

        self::assertSame(['discovered@example.test'], DiscoveredUserListener::$seen);
    }

    public function testEventsServiceProviderRegistersDispatcherAndBroadcaster(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $container = new Container();
        $container->instance(QueueDriver::class, new InMemoryQueueDriver());
        $app = new Application($basePath, $container, new Repository());
        $app->register(EventsServiceProvider::class);

        self::assertInstanceOf(Dispatcher::class, $app->container()->get(Dispatcher::class));
        self::assertInstanceOf(Broadcaster::class, $app->container()->get(Broadcaster::class));
        self::assertInstanceOf(EventDiscovery::class, $app->container()->get(EventDiscovery::class));
    }
}

class UserRegistered
{
    public function __construct(public readonly string $email)
    {
    }
}

final class StoppableUserRegistered extends UserRegistered implements StoppableEvent
{
    private bool $stopped = false;

    public function stop(): void
    {
        $this->stopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}

final class BroadcastUserRegistered extends UserRegistered implements Broadcastable
{
    public function broadcastOn(): string
    {
        return 'users';
    }

    public function broadcastWith(): array
    {
        return ['email' => $this->email];
    }
}

final class UserSubscriber implements EventSubscriber
{
    /**
     * @var list<string>
     */
    public array $seen = [];

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(UserRegistered::class, [$this, 'handleUserRegistered']);
    }

    public function handleUserRegistered(UserRegistered $event): void
    {
        $this->seen[] = $event->email;
    }
}

final class QueuedUserListener implements ShouldQueue
{
    /**
     * @var list<string>
     */
    public array $seen = [];

    public function handle(UserRegistered $event): void
    {
        $this->seen[] = $event->email;
    }
}

final class DiscoveredUserListener
{
    /**
     * @var list<string>
     */
    public static array $seen = [];

    public function handle(UserRegistered $event): void
    {
        self::$seen[] = $event->email;
    }
}
