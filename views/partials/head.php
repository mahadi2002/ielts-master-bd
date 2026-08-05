<?php
/**
 * @var string|null $title
 * @var string|null $description
 * @var string      $theme
 */
$pageTitle = isset($title) && $title !== ''
    ? $title . ' — ' . ($appName ?? 'IELTS Master BD')
    : ($appName ?? 'IELTS Master BD') . ' — শব্দ সোপান | দৈনিক IELTS Vocabulary';

$metaDescription = $description
    ?? 'প্রতিদিন IELTS Vocabulary শিখুন, Spaced Repetition দিয়ে মনে রাখুন, Exclusive Word Reward জিতুন।';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<meta name="theme-color" content="#1B4D3E">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="bn_BD">
