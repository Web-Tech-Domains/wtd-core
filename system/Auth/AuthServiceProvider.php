<?php

declare(strict_types=1);

namespace WTD\Auth;

use WTD\Hashing\PasswordHasher;
use WTD\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(PasswordHasher::class, fn (): PasswordHasher => new PasswordHasher());
        $this->container()->singleton(ApiTokenStore::class, fn (): ApiTokenStore => new ApiTokenStore());
        $this->container()->singleton(TokenBroker::class, fn (): TokenBroker => new TokenBroker());
        $this->container()->singleton(OAuthStateStore::class, fn (): OAuthStateStore => new OAuthStateStore());
        $this->container()->singleton(TotpService::class, fn (): TotpService => new TotpService());
        $this->container()->singleton(DeviceRegistry::class, fn (): DeviceRegistry => new DeviceRegistry());
        $this->container()->singleton(
            JwtService::class,
            fn (): JwtService => new JwtService((string) $this->app->config()->get('auth.jwt_secret', 'wtd-core')),
        );
    }
}
