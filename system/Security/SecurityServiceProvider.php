<?php

declare(strict_types=1);

namespace WTD\Security;

use WTD\Support\ServiceProvider;

final class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(Xss::class, fn (): Xss => new Xss());
        $this->container()->singleton(SqlIdentifier::class, fn (): SqlIdentifier => new SqlIdentifier());
        $this->container()->singleton(RateLimiter::class, fn (): RateLimiter => new RateLimiter());
        $this->container()->singleton(SecurityHeaders::class, fn (): SecurityHeaders => new SecurityHeaders());
        $this->container()->singleton(AuditLogger::class, fn (): AuditLogger => new AuditLogger());
        $this->container()->singleton(SecretsManager::class, fn (): SecretsManager => new SecretsManager());
        $this->container()->singleton(Cors::class, fn (): Cors => new Cors());
        $this->container()->singleton(TrustedProxy::class, fn (): TrustedProxy => new TrustedProxy());
        $this->container()->singleton(
            Encryption::class,
            fn (): Encryption => new Encryption((string) $this->app->config()->get('app.key', 'wtd-core')),
        );
        $this->container()->singleton(
            SignedUrl::class,
            fn (): SignedUrl => new SignedUrl((string) $this->app->config()->get('app.key', 'wtd-core')),
        );
    }
}
