<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Db;

header('Content-Type: application/xml; charset=utf-8');

$config = require dirname(__DIR__) . '/config.php';
$baseUrl = $config['app']['url'] ?: 'http://' . $_SERVER['HTTP_HOST'];

$words = Db::pdo()->query(
    "SELECT headword, created_at FROM words WHERE is_exclusive = FALSE ORDER BY created_at DESC LIMIT 5000"
)->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach (['/' => null, '/dictionary' => null, '/subscribe' => null] as $path => $_) {
    echo '  <url><loc>' . htmlspecialchars($baseUrl . $path, ENT_XML1) . '</loc></url>' . "\n";
}

foreach ($words as $word) {
    $loc = $baseUrl . '/dictionary/' . rawurlencode($word['headword']);
    $lastmod = date('Y-m-d', strtotime($word['created_at']));
    echo '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc><lastmod>' . $lastmod . '</lastmod></url>' . "\n";
}

echo '</urlset>';
