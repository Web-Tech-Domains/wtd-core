<?php

declare(strict_types=1);

namespace WTD\Config;

use RuntimeException;

/**
 * Loads PHP configuration files into the configuration repository.
 */
final class Loader
{
    public function __construct(private readonly Repository $repository)
    {
    }

    /**
     * Load all PHP config files from a directory.
     */
    public function loadDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) {
            throw new RuntimeException(sprintf('Unable to read configuration directory [%s].', $path));
        }

        sort($files);

        foreach ($files as $file) {
            $this->loadFile($file);
        }
    }

    /**
     * Load a single PHP config file.
     */
    public function loadFile(string $path): void
    {
        $values = require $path;

        if (!is_array($values)) {
            throw new RuntimeException(sprintf('Configuration file [%s] must return an array.', $path));
        }

        $namespace = basename($path, '.php');
        /** @var array<string, mixed> $values */
        $this->repository->merge($namespace, $values);
    }
}
