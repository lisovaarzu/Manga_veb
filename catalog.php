<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$ageRatings = getAgeRatings();
$ageRating = isset($_GET['age_rating']) ? trim($_GET['age_rating']) : '';

if (!in_array($ageRating, $ageRatings, true)) {
    $ageRating = '';
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY title ASC")->fetchAll();

$sql = "
    SELECT products.*, categories.title AS category_title
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    WHERE 1
";

$params = array();

if ($category_id > 0) {
    $sql .= " AND products.category_id = ?";
    $params[] = $category_id;
}

if ($search !== '') {
    $normalizedSearch = '%' . normalizeSearchText($search) . '%';

    $sql .= " AND (
        " . getNormalizedSearchSql('products.title') . " LIKE ?
        OR " . getNormalizedSearchSql('products.author') . " LIKE ?
        OR " . getNormalizedSearchSql('products.description') . " LIKE ?
        OR products.age_rating LIKE ?
    )";
    $params[] = $normalizedSearch;
    $params[] = $normalizedSearch;
    $params[] = $normalizedSearch;
    $params[] = $normalizedSearch;
}

if ($ageRating !== '') {
    $sql .= " AND products.age_rating = ?";
    $params[] = $ageRating;
}

$sql .= " ORDER BY products.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Каталог — MangaShop';
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Каталог манги</h1>
            <p class="page-subtitle">Выбери жанр, найди любимый тайтл и открой карточку товара.</p>
        </div>
    </div>

    <form class="catalog-filter" method="get">
        <div class="form-group">
            <label>Поиск</label>
            <input
                class="form-control"
                type="text"
                name="search"
                placeholder="Название, автор, ключевое слово или 16+"
                value="<?php echo e($search); ?>"
            >
        </div>

        <div class="form-group">
            <label>Категория</label>
            <select class="form-control" name="category_id">
                <option value="0">Все категории</option>

                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo e($category['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Возрастной рейтинг</label>
            <select class="form-control" name="age_rating">
                <option value="">Любой рейтинг</option>

                <?php foreach ($ageRatings as $rating): ?>
                    <option value="<?php echo e($rating); ?>" <?php echo $ageRating === $rating ? 'selected' : ''; ?>>
                        <?php echo e($rating); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-actions">
            <button class="btn" type="submit">Показать</button>
            <a href="/catalog.php" class="btn btn-secondary">Сбросить</a>
        </div>
    </form>

    <?php if (count($products) > 0): ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
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
                            <span class="badge age-badge <?php echo hasAdultAgeRating(isset($product['age_rating']) ? $product['age_rating'] : '16+') ? 'adult' : ''; ?>">
                                <?php echo e(isset($product['age_rating']) ? $product['age_rating'] : '16+'); ?>
                            </span>
                        </div>
                        <h3><?php echo e($product['title']); ?></h3>
                        <p class="product-card-author"><?php echo e($product['author']); ?></p>

                        <?php if (!empty($product['description'])): ?>
                            <p class="product-card-description"><?php echo e(excerptText($product['description'], 150)); ?></p>
                        <?php endif; ?>

                        <strong><?php echo e($product['price']); ?> ₽</strong>

                        <?php if ($product['stock'] > 0): ?>
                            <span class="badge pink">В наличии: <?php echo (int)$product['stock']; ?></span>
                        <?php else: ?>
                            <span class="badge red">Нет в наличии</span>
                        <?php endif; ?>

                        <a href="/product.php?id=<?php echo $product['id']; ?>" class="btn small">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            Товары не найдены. Попробуй изменить фильтр или поисковый запрос.
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
