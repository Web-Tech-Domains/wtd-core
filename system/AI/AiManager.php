<?php

declare(strict_types=1);

namespace WTD\AI;

use WTD\Config\Repository;

final class AiManager
{
    /**
     * @var array<string, AiProvider>
     */
    private array $providers = [];

    public function __construct(private readonly Repository $config)
    {
        $this->extend(new NullAiProvider());
    }

    public function extend(AiProvider $provider): void
    {
        $this->providers[$provider->name()] = $provider;
    }

    public function provider(?string $name = null): AiProvider
    {
        $name ??= $this->defaultProvider();

        return $this->providers[$name] ?? $this->providers['null'];
    }

    /**
     * @return list<string>
     */
    public function providers(): array
    {
        ksort($this->providers);

        return array_keys($this->providers);
    }

    private function defaultProvider(): string
    {
        $default = $this->config->get('ai.default', 'null');

        return is_scalar($default) ? (string) $default : 'null';
    }
}
