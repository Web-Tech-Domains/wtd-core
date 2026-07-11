<?php

declare(strict_types=1);

namespace WTD\Security;

use RuntimeException;

final class Encryption
{
    private const CIPHER = 'aes-256-gcm';

    public function __construct(private readonly string $key)
    {
    }

    public function encrypt(string $value): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $encrypted = openssl_encrypt($value, self::CIPHER, $this->key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($encrypted === false) {
            throw new RuntimeException('Unable to encrypt value.');
        }

        return base64_encode($iv . $tag . $encrypted);
    }

    public function decrypt(string $payload): string
    {
        $decoded = base64_decode($payload, true);

        if ($decoded === false || strlen($decoded) < 28) {
            throw new RuntimeException('Invalid encrypted payload.');
        }

        $iv = substr($decoded, 0, 12);
        $tag = substr($decoded, 12, 16);
        $encrypted = substr($decoded, 28);
        $plain = openssl_decrypt($encrypted, self::CIPHER, $this->key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($plain === false) {
            throw new RuntimeException('Unable to decrypt value.');
        }

        return $plain;
    }

    private function key(): string
    {
        return hash('sha256', $this->key, true);
    }
}
