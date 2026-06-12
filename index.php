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

$pageTitle = 'MangaShop — магазин манги';
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

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
                <?php $imageUrl = product_image_url($product['image']); ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($imageUrl): ?>
                            <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($product['title']); ?>">
                        <?php else: ?>
                            <span>Нет изображения</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <div class="product-meta">
                            <span class="category"><?php echo e($product['category_title']); ?></span>
                            <span class="badge age-badge <?php echo hasAdultAgeRating($product['age_rating']) ? 'adult' : ''; ?>">
                                <?php echo e(isset($product['age_rating']) ? $product['age_rating'] : '16+'); ?>
                            </span>
                        </div>
                        <h3><?php echo e($product['title']); ?></h3>
                        <p class="product-card-author"><?php echo e($product['author']); ?></p>

                        <?php if (!empty($product['description'])): ?>
                            <p class="product-card-description"><?php echo e(excerptText($product['description'], 120)); ?></p>
                        <?php endif; ?>

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
                <?php $imageUrl = product_image_url($product['image']); ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($imageUrl): ?>
                            <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($product['title']); ?>">
                        <?php else: ?>
                            <span>Нет изображения</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <div class="product-meta">
                            <span class="category"><?php echo e($product['category_title']); ?></span>
                            <span class="badge age-badge <?php echo hasAdultAgeRating($product['age_rating']) ? 'adult' : ''; ?>">
                                <?php echo e(isset($product['age_rating']) ? $product['age_rating'] : '16+'); ?>
                            </span>
                        </div>
                        <h3><?php echo e($product['title']); ?></h3>
                        <p class="product-card-author"><?php echo e($product['author']); ?></p>

                        <?php if (!empty($product['description'])): ?>
                            <p class="product-card-description"><?php echo e(excerptText($product['description'], 120)); ?></p>
                        <?php endif; ?>

                        <strong><?php echo e($product['price']); ?> ₽</strong>
                        <a href="/product.php?id=<?php echo $product['id']; ?>" class="btn small">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
