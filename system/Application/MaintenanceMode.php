<?php

declare(strict_types=1);

namespace WTD\Application;

use WTD\Filesystem\Filesystem;

/**
 * Tracks whether the application should reject normal traffic for maintenance.
 */
final class MaintenanceMode
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $path,
    ) {
    }

    /**
     * Enable maintenance mode.
     */
    public function enable(): void
    {
        $this->filesystem->put($this->path, json_encode([
            'enabled_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Disable maintenance mode.
     */
    public function disable(): void
    {
        $this->filesystem->delete($this->path);
    }

    /**
     * Determine whether maintenance mode is active.
     */
    public function enabled(): bool
    {
        return $this->filesystem->exists($this->path);
    }
}
