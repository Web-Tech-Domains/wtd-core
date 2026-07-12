<?php

declare(strict_types=1);

namespace WTD\View;

use WTD\Application\Application;
use WTD\Config\Repository;

/**
 * Resolves Vite development and production assets.
 */
final class AssetManager
{
    public function __construct(
        private readonly Application $app,
        private readonly Repository $config,
    ) {
    }

    public function url(string $entry): string
    {
        $manifest = $this->manifest();

        if ($manifest !== [] && isset($manifest[$entry]['file']) && is_string($manifest[$entry]['file'])) {
            return '/build/' . ltrim($manifest[$entry]['file'], '/');
        }

        if ($this->isHot()) {
            return rtrim($this->devServer(), '/') . '/' . ltrim($entry, '/');
        }

        return '';
    }

    /**
     * @param list<string> $entries
     */
    public function tags(array $entries): string
    {
        $tags = [];

        if ($this->isHot()) {
            $tags[] = '<script type="module" src="' . $this->escape(rtrim($this->devServer(), '/') . '/@vite/client') . '"></script>';
        }

        foreach ($entries as $entry) {
            foreach ($this->css($entry) as $css) {
                $tags[] = '<link rel="stylesheet" href="' . $this->escape($css) . '">';
            }

            $url = $this->url($entry);

            if ($url !== '') {
                $tags[] = '<script type="module" src="' . $this->escape($url) . '"></script>';
            }
        }

        return implode(PHP_EOL, $tags);
    }

    /**
     * @return list<string>
     */
    private function css(string $entry): array
    {
        $manifest = $this->manifest();
        $css = $manifest[$entry]['css'] ?? [];

        if (!is_array($css)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $path): ?string => is_string($path) ? '/build/' . ltrim($path, '/') : null,
            $css,
        )));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function manifest(): array
    {
        $path = $this->app->basePath($this->configString('assets.manifest', 'public/build/.vite/manifest.json'));

        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function isHot(): bool
    {
        return is_file($this->app->basePath($this->configString('assets.hot_file', 'public/hot')));
    }

    private function devServer(): string
    {
        return $this->configString('assets.dev_server', 'http://127.0.0.1:5173');
    }

    private function configString(string $key, string $default): string
    {
        $value = $this->config->get($key, $default);

        return is_scalar($value) ? (string) $value : $default;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
