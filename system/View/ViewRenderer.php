<?php

declare(strict_types=1);

namespace WTD\View;

use RuntimeException;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Filesystem\Filesystem;

/**
 * Renders simple file-based view templates.
 */
final class ViewRenderer
{
    public function __construct(
        private readonly Application $app,
        private readonly Repository $config,
        private readonly Filesystem $files,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): string
    {
        $path = $this->path($view);

        return $this->renderFile($path, $view, $data);
    }

    /**
     * Render a module view from modules/<Module>/Resources/views.
     *
     * @param array<string, mixed> $data
     */
    public function renderModule(string $module, string $view, array $data = []): string
    {
        $module = preg_replace('/[^A-Za-z0-9_]+/', '', basename(str_replace('\\', '/', $module))) ?? '';

        if ($module === '') {
            throw new RuntimeException('Module view name is invalid.');
        }

        return $this->renderFile(
            $this->app->basePath('modules/' . $module . '/Resources/views/' . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php'),
            $module . '::' . $view,
            $data,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderFile(string $path, string $name, array $data): string
    {
        if (!$this->files->exists($path)) {
            throw new RuntimeException(sprintf('View [%s] was not found.', $name));
        }

        $template = $this->files->get($path);
        $template = preg_replace_callback('/{!!\s*([A-Za-z_][A-Za-z0-9_.]*)\s*!!}/', static function (array $matches) use ($data): string {
            $value = self::value($data, $matches[1]);

            return is_scalar($value) ? (string) $value : '';
        }, $template) ?? $template;

        return preg_replace_callback('/{{\s*([A-Za-z_][A-Za-z0-9_.]*)\s*}}/', static function (array $matches) use ($data): string {
            $value = self::value($data, $matches[1]);

            return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES, 'UTF-8');
        }, $template) ?? $template;
    }

    private function path(string $view): string
    {
        $base = $this->config->get('view.path', 'resources/views');
        $extension = $this->config->get('view.extension', '.php');
        $base = is_scalar($base) ? (string) $base : 'resources/views';
        $extension = is_scalar($extension) ? (string) $extension : '.php';

        return $this->app->basePath(trim($base, '/\\') . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . $extension);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function value(array $data, string $key): mixed
    {
        $value = $data;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
