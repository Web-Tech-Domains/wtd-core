<?php

declare(strict_types=1);

namespace Tests\Cookie;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WTD\Cookie\Cookie;

final class CookieTest extends TestCase
{
    public function testCookieRendersHeaderValue(): void
    {
        $cookie = new Cookie('session', 'abc 123');

        self::assertSame('session', $cookie->name());
        self::assertSame('abc 123', $cookie->value());
        self::assertStringContainsString('session=abc%20123', $cookie->toHeader());
        self::assertStringContainsString('HttpOnly', $cookie->toHeader());
    }

    public function testCookieRejectsUnsafeNamesAndHeaderValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cookie("bad\r\nSet-Cookie: injected", 'value');
    }

    public function testSameSiteNoneRequiresSecureCookie(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cookie('session', 'value', sameSite: 'None');
    }

    public function testSecureSameSiteNoneCookieRendersSafely(): void
    {
        $cookie = new Cookie('session', 'value', secure: true, sameSite: 'None');

        self::assertStringContainsString('Secure', $cookie->toHeader());
        self::assertStringContainsString('SameSite=None', $cookie->toHeader());
    }
}
