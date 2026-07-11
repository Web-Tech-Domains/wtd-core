<?php

declare(strict_types=1);

namespace WTD\Auth;

final class TotpService
{
    public function secret(): string
    {
        return bin2hex(random_bytes(20));
    }

    public function code(string $secret, ?int $time = null): string
    {
        $counter = intdiv($time ?? time(), 30);
        $hash = hash_hmac('sha1', pack('J', $counter), $secret, true);
        $offset = ord($hash[19]) & 0x0f;
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function verify(string $secret, string $code, ?int $time = null, int $window = 1): bool
    {
        $time ??= time();

        for ($step = -$window; $step <= $window; $step++) {
            if (hash_equals($this->code($secret, $time + ($step * 30)), $code)) {
                return true;
            }
        }

        return false;
    }
}
