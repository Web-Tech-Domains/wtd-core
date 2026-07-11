<?php

declare(strict_types=1);

namespace WTD\Authorization;

final class Acl
{
    /**
     * @var array<string, array<string, list<string>>>
     */
    private array $rules = [];

    public function allow(string $subject, string $resource, string $action): void
    {
        $this->rules[$subject][$resource] ??= [];

        if (!in_array($action, $this->rules[$subject][$resource], true)) {
            $this->rules[$subject][$resource][] = $action;
        }
    }

    public function denies(string $subject, string $resource, string $action): bool
    {
        return !$this->allows($subject, $resource, $action);
    }

    public function allows(string $subject, string $resource, string $action): bool
    {
        return in_array($action, $this->rules[$subject][$resource] ?? [], true)
            || in_array('*', $this->rules[$subject][$resource] ?? [], true)
            || in_array($action, $this->rules[$subject]['*'] ?? [], true);
    }
}
