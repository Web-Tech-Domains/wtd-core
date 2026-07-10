<?php

declare(strict_types=1);

namespace WTD\Filesystem;

use RuntimeException;

/**
 * Provides small filesystem operations used by the framework core.
 */
final class Filesystem
{
    /**
     * Determine whether a path exists.
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Read a file into a string.
     */
    public function get(string $path): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Unable to read file [%s].', $path));
        }

        return $contents;
    }

    /**
     * Write contents to a file, creating the parent directory when necessary.
     */
    public function put(string $path, string $contents): void
    {
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create directory [%s].', $directory));
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException(sprintf('Unable to write file [%s].', $path));
        }
    }

    /**
     * Delete a file when it exists.
     */
    public function delete(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (!unlink($path)) {
            throw new RuntimeException(sprintf('Unable to delete file [%s].', $path));
        }
    }
}
