<?php

declare(strict_types=1);

namespace WTD\Storage;

use RuntimeException;

class InMemoryDisk implements StorageDisk
{
    /**
     * @var array<string, string>
     */
    private array $files = [];

    public function __construct(
        private readonly string $urlRoot,
        private readonly SignedUrlGenerator $signer = new SignedUrlGenerator('wtd-core'),
    ) {
    }

    public function put(string $path, string $contents): void
    {
        $this->files[$this->normalize($path)] = $contents;
    }

    public function get(string $path): string
    {
        $path = $this->normalize($path);

        return $this->files[$path] ?? throw new RuntimeException(sprintf('File [%s] does not exist.', $path));
    }

    public function exists(string $path): bool
    {
        return array_key_exists($this->normalize($path), $this->files);
    }

    public function delete(string $path): void
    {
        unset($this->files[$this->normalize($path)]);
    }

    public function url(string $path): string
    {
        return rtrim($this->urlRoot, '/') . '/' . $this->normalize($path);
    }

    public function temporaryUrl(string $path, int $expiresAt): string
    {
        return $this->signer->sign($this->url($path), $expiresAt);
    }

    private function normalize(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }
}
