<?php

declare(strict_types=1);

namespace WTD\Application;

/**
 * Tracks whether the application should reject normal traffic for maintenance.
 */
final class MaintenanceMode
{
    public function __construct(private bool $enabled = false)
    {
    }

    /**
     * Enable maintenance mode.
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable maintenance mode.
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Determine whether maintenance mode is active.
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }
}
