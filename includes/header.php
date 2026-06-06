<?php
if (!isset($pageTitle)) {
    $pageTitle = 'MangaShop';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="header">
    <a href="/" class="logo">MangaShop</a>

    <nav class="nav">
        <a href="/">Главная</a>
        <a href="/catalog.php">Каталог</a>

        <?php if (isAuth()): ?>
            <?php if (!isAdmin()): ?>
                <a href="/cart.php">Корзина</a>
                <a href="/orders.php">Мои заказы</a>
            <?php endif; ?>

            <?php if (isAdmin()): ?>
                <a href="/admin/index.php">Админка</a>
            <?php endif; ?>

            <a href="/logout.php">Выход</a>
        <?php else: ?>
            <a href="/login.php">Вход</a>
            <a href="/register.php">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>