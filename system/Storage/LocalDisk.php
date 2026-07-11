<?php

declare(strict_types=1);

namespace WTD\Storage;

use WTD\Filesystem\Filesystem;

final class LocalDisk implements StorageDisk
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $root,
        private readonly string $urlRoot = '/storage',
        private readonly SignedUrlGenerator $signer = new SignedUrlGenerator('wtd-core'),
    ) {
    }

    public function put(string $path, string $contents): void
    {
        $this->files->put($this->fullPath($path), $contents);
    }

    public function get(string $path): string
    {
        return $this->files->get($this->fullPath($path));
    }

    public function exists(string $path): bool
    {
        return $this->files->exists($this->fullPath($path));
    }

    public function delete(string $path): void
    {
        $this->files->delete($this->fullPath($path));
    }

    public function url(string $path): string
    {
        return rtrim($this->urlRoot, '/') . '/' . ltrim($path, '/');
    }

    public function temporaryUrl(string $path, int $expiresAt): string
    {
        return $this->signer->sign($this->url($path), $expiresAt);
    }

    private function fullPath(string $path): string
    {
        return rtrim($this->root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}
