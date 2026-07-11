<?php

declare(strict_types=1);

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Auth\ApiTokenGuard;
use WTD\Auth\ApiTokenStore;
use WTD\Auth\ArrayUserProvider;
use WTD\Auth\AuthServiceProvider;
use WTD\Auth\DeviceRegistry;
use WTD\Auth\GenericUser;
use WTD\Auth\JwtService;
use WTD\Auth\OAuthStateStore;
use WTD\Auth\SessionGuard;
use WTD\Auth\TokenBroker;
use WTD\Auth\TotpService;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\Hashing\PasswordHasher;
use WTD\Http\Request;
use WTD\Session\SessionStore;

final class AuthenticationTest extends TestCase
{
    public function testSessionGuardAttemptsLoginRememberMeAndLogout(): void
    {
        $hasher = new PasswordHasher();
        $provider = new ArrayUserProvider([[
            'id' => 1,
            'email' => 'user@example.test',
            'password' => $hasher->make('secret'),
        ]], $hasher);
        $session = new SessionStore(new Filesystem(), dirname(__DIR__) . '/tmp/auth-sessions');
        $session->start('auth');
        $guard = new SessionGuard($provider, $session);

        self::assertTrue($guard->attempt(['email' => 'user@example.test', 'password' => 'secret'], remember: true));
        self::assertTrue($guard->check());
        $user = $guard->user();

        self::assertNotNull($user);
        self::assertSame(1, $user->getAuthIdentifier());
        self::assertNotNull($user->getRememberToken());

        $guard->logout();

        self::assertFalse($guard->check());
        self::assertNull($session->get('auth_user_id'));
    }

    public function testJwtServiceIssuesAndValidatesTokens(): void
    {
        $jwt = new JwtService('secret');
        $token = $jwt->issue($this->user(), ttlSeconds: 60, claims: ['scope' => 'api']);
        $claims = $jwt->validate($token);

        self::assertIsArray($claims);
        self::assertSame(1, $claims['sub']);
        self::assertSame('api', $claims['scope']);
        self::assertNull((new JwtService('wrong'))->validate($token));
    }

    public function testApiTokenGuardAuthenticatesBearerTokens(): void
    {
        $store = new ApiTokenStore();
        $user = $this->user();
        $token = $store->issue($user);
        $guard = new ApiTokenGuard($store);

        self::assertSame($user, $guard->user(new Request('GET', '/', ['authorization' => 'Bearer ' . $token])));

        $store->revoke($token);

        self::assertNull($guard->user(new Request('GET', '/', ['authorization' => 'Bearer ' . $token])));
    }

    public function testTokenBrokerSupportsPasswordResetEmailVerificationAndMagicLinks(): void
    {
        $broker = new TokenBroker();
        $user = $this->user();
        $passwordReset = $broker->create($user);
        $emailVerification = $broker->create($user);
        $magicLink = $broker->create($user);

        self::assertTrue($broker->validate($user, $passwordReset));
        self::assertTrue($broker->consume($user, $emailVerification));
        self::assertFalse($broker->validate($user, $emailVerification));
        self::assertTrue($broker->validate($user, $magicLink));
    }

    public function testOAuthStateStoreConsumesKnownStateOnce(): void
    {
        $store = new OAuthStateStore();
        $state = $store->create();

        self::assertTrue($store->consume($state));
        self::assertFalse($store->consume($state));
    }

    public function testTotpServiceVerifiesMfaCodes(): void
    {
        $totp = new TotpService();
        $secret = 'fixed-secret';
        $time = 1_700_000_000;
        $code = $totp->code($secret, $time);

        self::assertTrue($totp->verify($secret, $code, $time));
        self::assertFalse($totp->verify($secret, '000000', $time));
    }

    public function testDeviceRegistryTracksAndForgetsDevices(): void
    {
        $registry = new DeviceRegistry();
        $user = $this->user();
        $id = $registry->remember($user, 'Chrome on Windows');

        self::assertCount(1, $registry->devices($user));
        self::assertSame('Chrome on Windows', $registry->devices($user)[0]['name']);

        $registry->forget($user, $id);

        self::assertSame([], $registry->devices($user));
    }

    public function testAuthServiceProviderRegistersAuthServices(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application(
            $basePath,
            new Container(),
            new Repository(['auth.jwt_secret' => 'configured']),
        );
        $app->register(AuthServiceProvider::class);

        self::assertInstanceOf(PasswordHasher::class, $app->container()->get(PasswordHasher::class));
        self::assertInstanceOf(JwtService::class, $app->container()->get(JwtService::class));
        self::assertInstanceOf(TokenBroker::class, $app->container()->get(TokenBroker::class));
    }

    private function user(): GenericUser
    {
        return new GenericUser([
            'id' => 1,
            'email' => 'user@example.test',
            'password' => 'hash',
        ]);
    }
}
