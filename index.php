<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$categories = $pdo->query("
    SELECT categories.*, COUNT(products.id) AS product_count
    FROM categories
    LEFT JOIN products ON products.category_id = categories.id
    GROUP BY categories.id
    ORDER BY categories.title ASC
    LIMIT 8
")->fetchAll();

$newProducts = $pdo->query("
    SELECT products.*, categories.title AS category_title
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    ORDER BY products.id DESC
    LIMIT 8
")->fetchAll();

$popularProducts = $pdo->query("
    SELECT products.*, categories.title AS category_title
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    WHERE products.stock > 0
    ORDER BY products.price DESC
    LIMIT 4
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>MangaShop — магазин манги</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="header">
    <a href="/" class="logo">MangaShop</a>

    <nav class="nav">
        <a href="/">Главная</a>
        <a href="/catalog.php">Каталог</a>

        <?php if (isAuth()): ?>
            <a href="/cart.php">Корзина</a>
            <a href="/orders.php">Мои заказы</a>

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

<section class="hero">
    <div>
        <span class="hero-label">Manga Store / Online Shop</span>
        <h1>Манга, коллекционные тома и любимые тайтлы</h1>
        <p>Каталог манги с жанрами, карточками товаров, корзиной, тестовыми заказами и live-панелью администратора.</p>

        <div class="hero-actions">
            <a href="/catalog.php" class="btn">Перейти в каталог</a>

            <?php if (!isAuth()): ?>
                <a href="/register.php" class="btn btn-secondary">Создать аккаунт</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<main class="container">
    <section class="home-section">
        <div class="page-title">
            <div>
                <h2>Жанры манги</h2>
                <p class="page-subtitle">Выбирай направление и переходи к нужной категории.</p>
            </div>

            <a href="/catalog.php" class="btn btn-secondary">Весь каталог</a>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <a href="/catalog.php?category_id=<?php echo $category['id']; ?>" class="category-card">
                    <strong><?php echo e($category['title']); ?></strong>
                    <span><?php echo (int)$category['product_count']; ?> товаров</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section">
        <div class="page-title">
            <div>
                <h2>Популярная манга</h2>
                <p class="page-subtitle">Подборка заметных и дорогих изданий магазина.</p>
            </div>
        </div>

        <div class="product-grid">
            <?php foreach ($popularProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/uploads/products/<?php echo e($product['image']); ?>" alt="<?php echo e($product['title']); ?>">
                        <?php else: ?>
                            <span>Нет изображения</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <span class="category"><?php echo e($product['category_title']); ?></span>
                        <h3><?php echo e($product['title']); ?></h3>
                        <p><?php echo e($product['author']); ?></p>
                        <strong><?php echo e($product['price']); ?> ₽</strong>
                        <a href="/product.php?id=<?php echo $product['id']; ?>" class="btn small">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section">
        <div class="page-title">
            <div>
                <h2>Новинки</h2>
                <p class="page-subtitle">Последние добавленные товары.</p>
            </div>
        </div>

        <div class="product-grid">
            <?php foreach ($newProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/uploads/products/<?php echo e($product['image']); ?>" alt="<?php echo e($product['title']); ?>">
                        <?php else: ?>
                            <span>Нет изображения</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <span class="category"><?php echo e($product['category_title']); ?></span>
                        <h3><?php echo e($product['title']); ?></h3>
                        <p><?php echo e($product['author']); ?></p>
                        <strong><?php echo e($product['price']); ?> ₽</strong>
                        <a href="/product.php?id=<?php echo $product['id']; ?>" class="btn small">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

</body>
</html>