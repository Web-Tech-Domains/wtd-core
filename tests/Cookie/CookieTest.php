<?php

declare(strict_types=1);

namespace Tests\Cookie;

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
}
