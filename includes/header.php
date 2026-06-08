<?php
if (!isset($pageTitle)) {
    $pageTitle = 'MangaShop';
}

if (!isset($headerLinks) || !is_array($headerLinks)) {
    $headerLinks = getDefaultHeaderLinks();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css?v=20260608-3">
</head>
<body>

<header class="header">
    <a href="/" class="logo">MangaShop</a>

    <nav class="nav">
        <?php foreach ($headerLinks as $link): ?>
            <a href="<?php echo e($link['href']); ?>">
                <?php echo e($link['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>
</header>
