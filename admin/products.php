<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
    SELECT products.*, categories.title AS category_title
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    WHERE 1
";

$params = array();

if ($search !== '') {
    $sql .= " AND (
        products.title LIKE ?
        OR products.author LIKE ?
        OR categories.title LIKE ?
    )";

    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$sql .= " ORDER BY products.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Товары — Админка MangaShop';
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Товары</h1>
            <p class="page-subtitle">Управление товарами магазина манги.</p>
        </div>

        <a href="/admin/product_edit.php" class="btn">Добавить товар</a>
    </div>

    <form class="catalog-filter" method="get">
        <div class="form-group">
            <label>Поиск товара</label>
            <input
                class="form-control"
                type="text"
                name="search"
                placeholder="Название, автор или категория"
                value="<?php echo e($search); ?>"
            >
        </div>

        <div class="filter-actions">
            <button class="btn" type="submit">Найти</button>
            <a href="/admin/products.php" class="btn btn-secondary">Сбросить</a>
        </div>
    </form>

    <?php if (count($products) > 0): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Обложка</th>
                        <th>Название</th>
                        <th>Автор</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Остаток</th>
                        <th>Действия</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo (int)$product['id']; ?></td>

                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img
                                        class="admin-thumb"
                                        src="/uploads/products/<?php echo e($product['image']); ?>"
                                        alt="<?php echo e($product['title']); ?>"
                                    >
                                <?php else: ?>
                                    <span class="badge red">Нет</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?php echo e($product['title']); ?></strong>
                            </td>

                            <td>
                                <?php echo !empty($product['author']) ? e($product['author']) : '—'; ?>
                            </td>

                            <td>
                                <span class="badge pink">
                                    <?php echo e($product['category_title']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo number_format($product['price'], 2, '.', ' '); ?> ₽
                            </td>

                            <td>
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="badge pink">
                                        <?php echo (int)$product['stock']; ?> шт.
                                    </span>
                                <?php else: ?>
                                    <span class="badge red">Нет</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        class="btn small"
                                        href="/product.php?id=<?php echo (int)$product['id']; ?>"
                                        target="_blank"
                                    >
                                        Смотреть
                                    </a>

                                    <a
                                        class="btn small"
                                        href="/admin/product_edit.php?id=<?php echo (int)$product['id']; ?>"
                                    >
                                        Редактировать
                                    </a>

                                    <form
                                        method="post"
                                        action="/admin/product_delete.php"
                                        onsubmit="return confirm('Удалить товар?');"
                                    >
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                                        <button class="btn small btn-danger" type="submit">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty">
            Товары не найдены.
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>