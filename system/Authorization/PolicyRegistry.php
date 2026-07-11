<?php

declare(strict_types=1);

namespace WTD\Authorization;

use BadMethodCallException;
use WTD\Auth\Authenticatable;

final class PolicyRegistry
{
    /**
     * @var array<class-string, object>
     */
    private array $policies = [];

    /**
     * @param class-string $class
     */
    public function policy(string $class, object $policy): void
    {
        $this->policies[$class] = $policy;
    }

    public function allows(?Authenticatable $user, string $ability, object $resource): bool
    {
        $policy = $this->policies[$resource::class] ?? null;

        if ($policy === null || !method_exists($policy, $ability)) {
            throw new BadMethodCallException(sprintf('Policy ability [%s] is not defined for [%s].', $ability, $resource::class));
        }

        return (bool) $policy->{$ability}($user, $resource);
    }
}
