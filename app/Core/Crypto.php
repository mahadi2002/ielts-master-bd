<?php

declare(strict_types=1);

namespace App\Core;

final class Crypto
{
    public static function hash(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public static function verify(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }

    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function otp(int $length): string
    {
        $max = (10 ** $length) - 1;
        $min = 10 ** ($length - 1);
        return (string) random_int($min, $max);
    }
}
