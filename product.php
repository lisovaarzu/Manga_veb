<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT products.*, categories.title AS category_title
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    WHERE products.id = ?
");
$stmt->execute(array($id));
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo 'Товар не найден';
    exit;
}

$relatedStmt = $pdo->prepare("
    SELECT products.*, categories.title AS category_title
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    WHERE products.category_id = ? AND products.id != ?
    ORDER BY products.id DESC
    LIMIT 4
");
$relatedStmt->execute(array($product['category_id'], $product['id']));
$relatedProducts = $relatedStmt->fetchAll();

$pageTitle = $product['title'] . ' — MangaShop';
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <a href="/catalog.php" class="back-link">← Вернуться в каталог</a>

    <section class="product-detail">
        <?php $mainImageUrl = product_image_url($product['image']); ?>
        <div class="product-detail-image">
            <?php if ($mainImageUrl): ?>
                <img src="<?php echo e($mainImageUrl); ?>" alt="<?php echo e($product['title']); ?>">
            <?php else: ?>
                <span>Нет изображения</span>
            <?php endif; ?>
        </div>

        <div class="product-detail-info">
            <div class="product-meta">
                <span class="category"><?php echo e($product['category_title']); ?></span>
                <span class="badge age-badge <?php echo hasAdultAgeRating($product['age_rating']) ? 'adult' : ''; ?>">
                    <?php echo e(isset($product['age_rating']) ? $product['age_rating'] : '16+'); ?>
                </span>
            </div>

            <h1><?php echo e($product['title']); ?></h1>

            <?php if (!empty($product['author'])): ?>
                <p class="product-author">Автор: <?php echo e($product['author']); ?></p>
            <?php endif; ?>

            <div class="product-price">
                <?php echo e($product['price']); ?> ₽
            </div>

            <?php if ($product['stock'] > 0): ?>
                <span class="badge pink">В наличии: <?php echo (int)$product['stock']; ?></span>
            <?php else: ?>
                <span class="badge red">Нет в наличии</span>
            <?php endif; ?>

            <div class="product-description">
                <h3>Описание</h3>
                <p><?php echo nl2br(e($product['description'])); ?></p>
            </div>

            <?php if (isAuth() && !isAdmin()): ?>
                <?php if ($product['stock'] > 0): ?>
                    <form action="/ajax/add_to_cart.php" method="post" class="add-cart-form">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                        <div class="form-group quantity-group">
                            <label>Количество</label>
                            <input class="form-control" type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>">
                        </div>

                        <button class="btn" type="submit">Добавить в корзину</button>
                    </form>
                <?php endif; ?>
            <?php elseif (!isAuth()): ?>
                <div class="auth-note">
                    Чтобы добавить товар в корзину, нужно
                    <a href="/login.php">войти</a>
                    или
                    <a href="/register.php">зарегистрироваться</a>.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (count($relatedProducts) > 0): ?>
        <section class="related-section">
            <h2>Похожие товары</h2>

            <div class="product-grid">
                <?php foreach ($relatedProducts as $item): ?>
                    <?php $imageUrl = product_image_url($item['image']); ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($imageUrl): ?>
                                <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($item['title']); ?>">
                            <?php else: ?>
                                <span>Нет изображения</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-info">
                            <div class="product-meta">
                                <span class="category"><?php echo e($item['category_title']); ?></span>
                                <span class="badge age-badge <?php echo hasAdultAgeRating($item['age_rating']) ? 'adult' : ''; ?>">
                                    <?php echo e(isset($item['age_rating']) ? $item['age_rating'] : '16+'); ?>
                                </span>
                            </div>
                            <h3><?php echo e($item['title']); ?></h3>
                            <p class="product-card-author"><?php echo e($item['author']); ?></p>

                            <?php if (!empty($item['description'])): ?>
                                <p class="product-card-description"><?php echo e(excerptText($item['description'], 110)); ?></p>
                            <?php endif; ?>

                            <strong><?php echo e($item['price']); ?> ₽</strong>
                            <a href="/product.php?id=<?php echo $item['id']; ?>" class="btn small">Подробнее</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
