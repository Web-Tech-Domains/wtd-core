<?php

declare(strict_types=1);

namespace Tests\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\Hashing\PasswordHasher;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Security\AuditLogger;
use WTD\Security\Cors;
use WTD\Security\CsrfTokenManager;
use WTD\Security\Encryption;
use WTD\Security\RateLimiter;
use WTD\Security\SecurityHeaders;
use WTD\Security\SecurityHeadersMiddleware;
use WTD\Security\SecurityServiceProvider;
use WTD\Security\SecretsManager;
use WTD\Security\SignedUrl;
use WTD\Security\SqlIdentifier;
use WTD\Security\TrustedProxy;
use WTD\Security\VerifyCsrfToken;
use WTD\Security\Xss;
use WTD\Session\SessionStore;

final class SecurityTest extends TestCase
{
    public function testCsrfMiddlewareRejectsInvalidTokensAndAllowsValidTokens(): void
    {
        $session = new SessionStore(new Filesystem(), dirname(__DIR__) . '/tmp/security-sessions');
        $session->start('csrf-session-1234');
        $manager = new CsrfTokenManager($session);
        $middleware = new VerifyCsrfToken($manager);
        $token = $manager->token();

        $rejected = $middleware->handle(new Request('POST', '/', body: ['_token' => 'bad']), static fn (): Response => Response::make('OK'));
        $allowed = $middleware->handle(new Request('POST', '/', body: ['_token' => $token]), static fn (): Response => Response::make('OK'));

        self::assertSame(419, $rejected->status());
        self::assertSame(200, $allowed->status());
    }

    public function testXssEscapesStringsAndNestedArrays(): void
    {
        $xss = new Xss();

        self::assertSame('&lt;script&gt;', $xss->escape('<script>'));
        self::assertSame(['name' => '&lt;b&gt;'], $xss->escapeArray(['name' => '<b>']));
    }

    public function testSqlIdentifierRejectsUnsafeIdentifiers(): void
    {
        $identifiers = new SqlIdentifier();

        self::assertSame('users.name', $identifiers->assertSafe('users.name'));

        $this->expectException(InvalidArgumentException::class);

        $identifiers->assertSafe('users; drop table users');
    }

    public function testEncryptionRoundTripsValues(): void
    {
        $encryption = new Encryption('secret');
        $payload = $encryption->encrypt('classified');

        self::assertNotSame('classified', $payload);
        self::assertSame('classified', $encryption->decrypt($payload));
    }

    public function testHashingVerifiesPasswords(): void
    {
        $hasher = new PasswordHasher();
        $hash = $hasher->make('secret');

        self::assertTrue($hasher->verify('secret', $hash));
        self::assertFalse($hasher->verify('wrong', $hash));
    }

    public function testRateLimiterTracksAttempts(): void
    {
        $limiter = new RateLimiter();

        self::assertTrue($limiter->hit('login:1', 2, 60));
        self::assertTrue($limiter->hit('login:1', 2, 60));
        self::assertFalse($limiter->hit('login:1', 2, 60));
        self::assertSame(0, $limiter->remaining('login:1', 2));
    }

    public function testCorsSecurityHeadersSignedUrlsTrustedProxyAuditAndSecrets(): void
    {
        $response = Response::make('OK');
        $headers = (new SecurityHeaders())->apply((new Cors())->apply($response, 'https://example.test'))->headers();

        self::assertSame('https://example.test', $headers['Access-Control-Allow-Origin']);
        self::assertSame('SAMEORIGIN', $headers['X-Frame-Options']);
        self::assertSame('nosniff', $headers['X-Content-Type-Options']);
        self::assertArrayHasKey('Strict-Transport-Security', $headers);
        self::assertArrayHasKey('Permissions-Policy', $headers);
        self::assertStringContainsString("object-src 'none'", $headers['Content-Security-Policy']);

        $signed = new SignedUrl('secret');
        $url = $signed->sign('/download?id=1', time() + 60);
        self::assertTrue($signed->validate($url));

        $request = new Request('GET', '/', ['x-forwarded-for' => '203.0.113.10'], server: ['REMOTE_ADDR' => '127.0.0.1']);
        self::assertSame('203.0.113.10', (new TrustedProxy())->clientIp($request, ['127.0.0.1']));

        $audit = new AuditLogger();
        $audit->record('login', ['user_id' => 1]);
        self::assertSame('login', $audit->events()[0]['event']);

        $secrets = new SecretsManager(['API_KEY' => 'abcdef']);
        self::assertSame('abcdef', $secrets->get('API_KEY'));
        self::assertSame('ab**ef', $secrets->mask('abcdef'));
    }

    public function testSecurityHeadersMiddlewareAppliesHeadersToResponses(): void
    {
        $response = (new SecurityHeadersMiddleware(new SecurityHeaders()))->handle(
            new Request('GET', '/'),
            static fn (): Response => Response::make('OK'),
        );

        self::assertSame('SAMEORIGIN', $response->headers()['X-Frame-Options']);
        self::assertArrayHasKey('Content-Security-Policy', $response->headers());
    }

    public function testSecurityServiceProviderRegistersServices(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application($basePath, new Container(), new Repository(['app.key' => 'secret']));
        $app->register(SecurityServiceProvider::class);

        self::assertInstanceOf(Encryption::class, $app->container()->get(Encryption::class));
        self::assertInstanceOf(RateLimiter::class, $app->container()->get(RateLimiter::class));
        self::assertInstanceOf(SecretsManager::class, $app->container()->get(SecretsManager::class));
    }
}
