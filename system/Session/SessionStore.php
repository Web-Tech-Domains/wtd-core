<?php

declare(strict_types=1);

namespace WTD\Session;

use WTD\Filesystem\Filesystem;

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
        $this->id = $id !== null && $id !== '' ? $id : bin2hex(random_bytes(20));
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
}
