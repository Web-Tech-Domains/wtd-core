<?php

declare(strict_types=1);

namespace WTD\Storage;

interface StorageDisk
{
    public function put(string $path, string $contents): void;

    public function get(string $path): string;

    public function exists(string $path): bool;

    public function delete(string $path): void;

    public function url(string $path): string;

    public function temporaryUrl(string $path, int $expiresAt): string;
}
