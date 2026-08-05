<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;

/**
 * Best-effort outbound email. Uses PHP's mail() rather than pulling in an
 * SMTP library — every cPanel host this is meant to run on already has a
 * working local MTA wired up for it. If SUPPORT_EMAIL is blank, or mail()
 * fails, this quietly no-ops: a notification failing must never take the
 * contact form (or anything else) down with it. The admin inbox is the
 * actual source of truth; email is just a heads-up.
 */
final class Notifier
{
    public static function contactReceived(int $messageId, string $name, string $preview): void
    {
        $to = (string) config('app.support_email', '');
        if ($to === '') {
            return;
        }

        $subject = (string) config('app.name') . ' — নতুন Contact Message #' . $messageId;
        $body    = "নাম: {$name}\n\n{$preview}\n\nদেখুন: " . (string) config('app.url') . '/admin/contact/' . $messageId;
        $headers = 'From: no-reply@' . self::domain() . "\r\nContent-Type: text/plain; charset=UTF-8";

        try {
            if (!@mail($to, $subject, $body, $headers)) {
                Logger::warning('notifier.mail_failed', ['to' => $to, 'message_id' => $messageId]);
            }
        } catch (\Throwable $e) {
            Logger::warning('notifier.mail_exception', ['error' => $e->getMessage()]);
        }
    }

    private static function domain(): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);
        return is_string($host) && $host !== '' ? $host : 'localhost';
    }
}
