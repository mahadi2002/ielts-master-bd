<?php
declare(strict_types=1);

namespace App\Core;

/**
 * A small, deliberately limited Markdown-to-HTML converter for guide bodies.
 * Only ever called on admin-authored content (guides) — never on end-user
 * text (Q&A questions/answers stay plain, escaped strings). Escapes first,
 * then re-introduces only the handful of tags this supports, so there is no
 * path from a stored string to an unescaped <script>.
 */
final class Markdown
{
    public static function toHtml(string $raw): string
    {
        $escaped = e($raw);
        $lines   = explode("\n", $escaped);
        $html    = [];
        $inList  = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                continue;
            }

            if (preg_match('/^(#{2,3})\s+(.*)$/', $trimmed, $m)) {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                $level  = strlen($m[1]) + 1; // ## -> h3, ### -> h4
                $html[] = "<h{$level}>" . self::inline($m[2]) . "</h{$level}>";
                continue;
            }

            if (preg_match('/^[-*]\s+(.*)$/', $trimmed, $m)) {
                if (!$inList) {
                    $html[] = '<ul>';
                    $inList = true;
                }
                $html[] = '<li>' . self::inline($m[1]) . '</li>';
                continue;
            }

            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }

            $html[] = '<p>' . self::inline($trimmed) . '</p>';
        }

        if ($inList) {
            $html[] = '</ul>';
        }

        return implode("\n", $html);
    }

    /** Bold/italic only, on already-escaped text. */
    private static function inline(string $escaped): string
    {
        $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $escaped) ?? $escaped;
        return $escaped;
    }
}
