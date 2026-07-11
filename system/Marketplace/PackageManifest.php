<?php

declare(strict_types=1);

namespace WTD\Marketplace;

/**
 * Describes a framework package available to the local marketplace.
 */
final class PackageManifest
{
    /**
     * @param list<string> $providers
     * @param array<string, string> $config
     * @param list<string> $keywords
     */
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly string $description = '',
        public readonly array $providers = [],
        public readonly array $config = [],
        public readonly array $keywords = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::string($data, 'name', 'unknown/package'),
            self::string($data, 'version', '0.0.0'),
            self::string($data, 'description', ''),
            self::strings($data['providers'] ?? []),
            self::map($data['config'] ?? []),
            self::strings($data['keywords'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'providers' => $this->providers,
            'config' => $this->config,
            'keywords' => $this->keywords,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function string(array $data, string $key, string $default): string
    {
        $value = $data[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @return list<string>
     */
    private static function strings(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $value): ?string => is_scalar($value) ? (string) $value : null,
            $values,
        )));
    }

    /**
     * @return array<string, string>
     */
    private static function map(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $mapped = [];

        foreach ($values as $source => $target) {
            if (!is_scalar($source) || !is_scalar($target)) {
                continue;
            }

            $mapped[(string) $source] = (string) $target;
        }

        return $mapped;
    }
}
