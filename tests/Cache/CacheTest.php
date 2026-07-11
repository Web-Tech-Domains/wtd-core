<?php

declare(strict_types=1);

namespace Tests\Cache;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Cache\CacheManager;
use WTD\Cache\CacheRepository;
use WTD\Cache\CacheServiceProvider;
use WTD\Cache\FileStore;
use WTD\Cache\MemcachedStore;
use WTD\Cache\RedisStore;
use WTD\Config\Repository;
use WTD\Container\Container;

final class CacheTest extends TestCase
{
    public function testCacheStoresValuesAndRecordsEvents(): void
    {
        $cache = new CacheRepository(new FileStore());
        $cache->put('name', 'WTD', 60);

        self::assertSame('WTD', $cache->get('name'));
        self::assertSame('fallback', $cache->get('missing', 'fallback'));
        self::assertSame(['written', 'hit', 'missed'], array_map(
            static fn ($event): string => $event->type,
            $cache->events()->events(),
        ));
    }

    public function testCacheRememberForgetAndFlush(): void
    {
        $cache = new CacheRepository(new FileStore());
        $calls = 0;

        self::assertSame('value', $cache->remember('remembered', 60, static function () use (&$calls): string {
            $calls++;

            return 'value';
        }));
        self::assertSame('value', $cache->remember('remembered', 60, static function () use (&$calls): string {
            $calls++;

            return 'new';
        }));
        self::assertSame(1, $calls);

        $cache->forget('remembered');
        self::assertSame('missing', $cache->get('remembered', 'missing'));

        $cache->put('flush', true);
        $cache->flush();
        self::assertSame(false, $cache->get('flush', false));
    }

    public function testTaggedCacheCanFlushTagNamespace(): void
    {
        $cache = new CacheRepository(new FileStore());
        $tagged = $cache->tags(['users']);
        $tagged->put('count', 5);

        self::assertSame(5, $tagged->get('count'));

        $tagged->flush();

        self::assertSame(0, $tagged->get('count', 0));
    }

    public function testAtomicLocksCanBeAcquiredAndReleased(): void
    {
        $cache = new CacheRepository(new FileStore());
        $lock = $cache->lock('deploy');

        self::assertTrue($lock->acquire());
        self::assertFalse($cache->lock('deploy')->acquire());

        $lock->release();

        self::assertTrue($cache->lock('deploy')->acquire());
    }

    public function testCacheManagerProvidesConfiguredStores(): void
    {
        $manager = new CacheManager('file');

        self::assertInstanceOf(CacheRepository::class, $manager->store());
        self::assertInstanceOf(RedisStore::class, $this->store($manager->store('redis')));
        self::assertInstanceOf(MemcachedStore::class, $this->store($manager->store('memcached')));
    }

    public function testCacheServiceProviderRegistersCacheRepository(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application($basePath, new Container(), new Repository(['cache.default' => 'redis']));
        $app->register(CacheServiceProvider::class);

        self::assertInstanceOf(CacheManager::class, $app->container()->get(CacheManager::class));
        self::assertInstanceOf(CacheRepository::class, $app->container()->get(CacheRepository::class));
    }

    private function store(CacheRepository $repository): object
    {
        $reflection = new \ReflectionProperty($repository, 'store');

        return $reflection->getValue($repository);
    }
}
