<?php

declare(strict_types=1);

namespace WTD\Cache;

final class CacheRepository
{
    /**
     * @var array<string, int>
     */
    private array $tagVersions = [];

    public function __construct(
        private readonly CacheStore $store,
        private readonly CacheEventRecorder $events = new CacheEventRecorder(),
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->store->get($key, $default);
        $this->events->record($value === $default ? 'missed' : 'hit', $key);

        return $value;
    }

    public function put(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        $this->store->put($key, $value, $ttlSeconds);
        $this->events->record('written', $key);
    }

    public function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        if ($this->store->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->put($key, $value, $ttlSeconds);

        return $value;
    }

    public function forget(string $key): void
    {
        $this->store->forget($key);
        $this->events->record('forgotten', $key);
    }

    public function flush(): void
    {
        $this->store->flush();
        $this->events->record('flushed', '*');
    }

    public function lock(string $name, int $ttlSeconds = 60): Lock
    {
        return new Lock($this->store, $name, $ttlSeconds);
    }

    /**
     * @param list<string> $tags
     */
    public function tags(array $tags): TaggedCache
    {
        return new TaggedCache($this, $tags);
    }

    public function events(): CacheEventRecorder
    {
        return $this->events;
    }

    public function tagVersion(string $tag): int
    {
        return $this->tagVersions[$tag] ?? 1;
    }

    public function incrementTagVersion(string $tag): void
    {
        $this->tagVersions[$tag] = $this->tagVersion($tag) + 1;
        $this->events->record('tag-flushed', $tag);
    }
}
