<?php

declare(strict_types=1);

namespace WTD\Authorization;

use WTD\Support\ServiceProvider;

final class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(RbacManager::class, fn (): RbacManager => new RbacManager());
        $this->container()->singleton(RoleCache::class, fn (): RoleCache => new RoleCache());
        $this->container()->singleton(Gate::class, fn (): Gate => new Gate());
        $this->container()->singleton(PolicyRegistry::class, fn (): PolicyRegistry => new PolicyRegistry());
        $this->container()->singleton(Acl::class, fn (): Acl => new Acl());
    }
}
