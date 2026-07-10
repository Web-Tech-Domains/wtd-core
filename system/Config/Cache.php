<?php

declare(strict_types=1);

namespace WTD\Config;

use RuntimeException;
use WTD\Filesystem\Filesystem;

/**
 * Reads and writes the optimized configuration cache file.
 */
final class Cache
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $path,
    ) {
    }

    /**
     * Determine whether cached configuration exists.
     */
    public function exists(): bool
    {
        return $this->filesystem->exists($this->path);
    }

    /**
     * Load cached configuration values.
     *
     * @return array<string, mixed>
     */
    public function load(): array
    {
        if (!$this->exists()) {
            return [];
        }

        $items = require $this->path;

        if (!is_array($items)) {
            throw new RuntimeException(sprintf('Configuration cache [%s] must return an array.', $this->path));
        }

        /** @var array<string, mixed> $items */
        return $items;
    }

    /**
     * Store configuration values in an optimized PHP file.
     *
     * @param array<string, mixed> $items
     */
    public function write(array $items): void
    {
        $this->filesystem->put(
            $this->path,
            "<?php\n\nreturn " . var_export($items, true) . ";\n",
        );
    }

    /**
     * Remove the configuration cache file.
     */
    public function clear(): void
    {
        $this->filesystem->delete($this->path);
    }
}
