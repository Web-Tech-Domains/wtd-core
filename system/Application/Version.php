<?php

declare(strict_types=1);

namespace WTD\Application;

/**
 * Exposes framework version metadata.
 */
final class Version
{
    /**
     * Return the current framework semantic version.
     */
    public function current(): string
    {
        return Application::VERSION;
    }
}
