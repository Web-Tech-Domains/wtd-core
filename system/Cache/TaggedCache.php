<?php

declare(strict_types=1);

namespace WTD\Cache;

final class TaggedCache
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly array $tags,
    ) {
    }

    public function put(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        $this->cache->put($this->taggedKey($key), $value, $ttlSeconds);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->taggedKey($key), $default);
    }

    public function flush(): void
    {
        foreach ($this->tags as $tag) {
            $this->cache->incrementTagVersion($tag);
        }
    }

    private function taggedKey(string $key): string
    {
        $versions = array_map(fn (string $tag): string => $tag . ':' . $this->cache->tagVersion($tag), $this->tags);

        return 'tagged:' . implode('|', $versions) . ':' . $key;
    }
}
