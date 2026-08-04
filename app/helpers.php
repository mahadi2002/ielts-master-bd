<?php

declare(strict_types=1);

/**
 * Mandatory output-escape helper for all user-supplied / DB-sourced text in views (XSS guard).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): string
{
    $old = $_SESSION['_old'][$key] ?? $default;
    return e((string) $old);
}

function csrf_field(string $token): string
{
    return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/**
 * Bangla messages for error codes carried via query string on anonymous
 * flows (invalid mobile, OTP failures) — anonymous requests don't persist
 * $_SESSION (sessions.user_id is NOT NULL), so flash messages can't be used
 * before login.
 */
function authErrorMessage(?string $code): ?string
{
    return match ($code) {
        'invalid_mobile' => 'শুধু Robi (016/019) ও Airtel (017) নাম্বার সাপোর্টেড। সঠিক নাম্বার দিন।',
        'expired' => 'OTP মেয়াদ শেষ। আবার পাঠান।',
        'too_many_attempts' => 'অনেকবার ভুল চেষ্টা হয়েছে। ৫ মিনিট পর আবার চেষ্টা করুন।',
        'wrong_otp' => 'ভুল OTP দিয়েছেন। আবার চেষ্টা করুন।',
        'rate_limited' => 'অনেকবার চেষ্টা করা হয়েছে। কিছুক্ষণ পর আবার চেষ্টা করুন।',
        'subscribe_failed' => 'Subscription সম্পন্ন হয়নি। আবার চেষ্টা করুন।',
        default => null,
    };
}
