<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private static ?array $config = null;

    public static function boot(array $config): void
    {
        self::$config = $config;
    }

    public static function render(string $template, array $data = [], ?string $layout = 'layout'): string
    {
        $data['config'] = self::$config;
        $data['auth'] = Session::user();
        $data['csrfToken'] = Csrf::token();
        $data['flash'] = Session::flash('message');
        $data['flashType'] = Session::flash('message_type') ?? 'info';
        $data['streakCount'] = $data['auth'] ? (\App\Models\Streak::forUser($data['auth']['id'])['current_streak'] ?? 0) : 0;

        $content = self::capture(BASE_PATH . '/app/Views/' . $template . '.php', $data);

        if ($layout === null) {
            return $content;
        }

        $data['content'] = $content;
        return self::capture(BASE_PATH . '/app/Views/' . $layout . '.php', $data);
    }

    private static function capture(string $path, array $data): string
    {
        if (!is_file($path)) {
            throw new \RuntimeException("View not found: {$path}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
