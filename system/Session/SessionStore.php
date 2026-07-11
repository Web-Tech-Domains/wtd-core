<?php

declare(strict_types=1);

namespace WTD\Session;

use WTD\Filesystem\Filesystem;
use InvalidArgumentException;

/**
 * Stores session data in local files.
 */
final class SessionStore
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $path,
        private ?string $id = null,
    ) {
    }

    /**
     * Start a session by ID or create a new one.
     */
    public function start(?string $id = null): void
    {
        $this->id = $this->normalizeId($id);
        $file = $this->filePath();

        if (!$this->filesystem->exists($file)) {
            $this->data = [];
            return;
        }

        $payload = unserialize($this->filesystem->get($file), ['allowed_classes' => false]);
        $this->data = is_array($payload) ? $payload : [];
    }

    /**
     * Return the active session ID.
     */
    public function id(): string
    {
        if ($this->id === null) {
            $this->start();
        }

        return (string) $this->id;
    }

    /**
     * Get a session value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a session value.
     */
    public function put(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Remove a session value.
     */
    public function forget(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Regenerate the session ID, optionally deleting the old persisted session.
     */
    public function regenerate(bool $destroy = true): string
    {
        $oldFile = $this->id === null ? null : $this->filePath();
        $this->id = bin2hex(random_bytes(20));

        if ($destroy && $oldFile !== null) {
            $this->filesystem->delete($oldFile);
        }

        return $this->id;
    }

    /**
     * Store data for the next request.
     */
    public function flash(string $key, mixed $value): void
    {
        $this->data['_flash'][$key] = $value;
    }

    /**
     * Pull flashed data and remove it from the session.
     */
    public function pullFlash(string $key, mixed $default = null): mixed
    {
        $value = is_array($this->data['_flash'] ?? null) && array_key_exists($key, $this->data['_flash'])
            ? $this->data['_flash'][$key]
            : $default;

        unset($this->data['_flash'][$key]);

        if (($this->data['_flash'] ?? []) === []) {
            unset($this->data['_flash']);
        }

        return $value;
    }

    /**
     * Save the active session.
     */
    public function save(): void
    {
        $this->filesystem->put($this->filePath(), serialize($this->data));
    }

    /**
     * Return the current session data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Return the file path for the active session.
     */
    private function filePath(): string
    {
        return rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->id();
    }

    private function normalizeId(?string $id): string
    {
        if ($id === null || $id === '') {
            return bin2hex(random_bytes(20));
        }

        if (preg_match('/^[A-Za-z0-9,-]{16,128}$/', $id) !== 1) {
            throw new InvalidArgumentException('Session ID contains invalid characters.');
        }

        return $id;
    }
}
