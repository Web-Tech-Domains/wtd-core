<?php

declare(strict_types=1);

namespace WTD\Hooks;

use WTD\Support\ServiceProvider;

final class HookServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, true>
     */
    private static array $loadedFiles = [];

    public function register(): void
    {
        $this->container()->singleton(HookManager::class, static fn (): HookManager => new HookManager());
    }

    public function boot(): void
    {
        if (!$this->enabled()) {
            return;
        }

        /** @var HookManager $hooks */
        $hooks = $this->container()->get(HookManager::class);

        foreach ($this->files() as $file) {
            $this->loadHookFile($hooks, $file);
        }

        $hooks->doAction('hooks.loaded', $this->app);
    }

    private function enabled(): bool
    {
        return (bool) $this->app->config()->get('hooks.enabled', true);
    }

    /**
     * @return list<string>
     */
    private function files(): array
    {
        $files = $this->app->config()->get('hooks.files', ['app/Hooks.php']);

        if (!is_array($files)) {
            return [];
        }

        $resolved = [];

        foreach ($files as $file) {
            if (!is_string($file) || $file === '') {
                continue;
            }

            $resolved[] = $this->isAbsolutePath($file)
                ? $file
                : $this->app->basePath($file);
        }

        return $resolved;
    }

    private function loadHookFile(HookManager $hooks, string $file): void
    {
        if (isset(self::$loadedFiles[$file]) || !is_file($file) || !is_readable($file)) {
            return;
        }

        self::$loadedFiles[$file] = true;

        require $file;
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }
}
