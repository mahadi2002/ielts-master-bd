<?php
declare(strict_types=1);

namespace App\Support;

/**
 * MSISDN normalisation + operator lookup.
 * Every prefix decision reads config/operators.php — the single source of truth.
 */
final class Operators
{
    /**
     * Accepts 01XXXXXXXXX, 8801XXXXXXXXX, +8801XXXXXXXXX, 001XXXXXXXXX.
     * Returns the canonical 11-digit local form, or null if it is not one.
     */
    public static function normalize(string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', $input) ?? '';

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '00880')) {
            $digits = substr($digits, 5);
        }

        if (strlen($digits) === 10 && $digits[0] === '1') {
            $digits = '0' . $digits;
        }

        return preg_match('/^01[0-9]{9}$/', $digits) === 1 ? $digits : null;
    }

    /** 'robi' | 'airtel' | 'grameenphone' | … | null for an unmapped prefix. */
    public static function detect(string $msisdn): ?string
    {
        $normalized = self::normalize($msisdn);
        if ($normalized === null) {
            return null;
        }
        $map = (array) config('operators.map', []);
        return $map[substr($normalized, 0, 3)] ?? null;
    }

    public static function isAllowed(string $msisdn): bool
    {
        $operator = self::detect($msisdn);
        return $operator !== null && in_array($operator, (array) config('operators.allowed', []), true);
    }

    /** Last 4 digits — the only part ever displayed in the UI. */
    public static function last4(string $msisdn): string
    {
        return substr((string) self::normalize($msisdn), -4);
    }

    /** 01711****89 — for the OTP screen, where the user must recognise their own number. */
    public static function mask(string $msisdn): string
    {
        $n = self::normalize($msisdn);
        if ($n === null) {
            return '';
        }
        return substr($n, 0, 5) . '****' . substr($n, -2);
    }

    public static function displayNote(): string
    {
        return (string) config('operators.display_note', '');
    }
}
