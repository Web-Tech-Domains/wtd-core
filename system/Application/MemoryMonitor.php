<?php

declare(strict_types=1);

namespace WTD\Application;

/**
 * Reports PHP process memory usage.
 */
final class MemoryMonitor
{
    /**
     * Return the current memory usage in bytes.
     */
    public function usage(): int
    {
        return memory_get_usage(true);
    }

    /**
     * Return the peak memory usage in bytes.
     */
    public function peak(): int
    {
        return memory_get_peak_usage(true);
    }
}
