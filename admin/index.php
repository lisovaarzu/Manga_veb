<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalUsers = $pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'user' AND is_deleted = 0
")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalSales = $pdo->query("
    SELECT IFNULL(SUM(total_price), 0)
    FROM orders
    WHERE status <> 'Отменён'
")->fetchColumn();

$latestOrders = $pdo->query("
    SELECT orders.*, users.name AS user_name
    FROM orders
    INNER JOIN users ON orders.user_id = users.id
    ORDER BY orders.id DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Админка — MangaShop';
$headerLinks = getAdminHeaderLinks();
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Панель администратора</h1>
            <p class="page-subtitle">Статистика магазина и быстрый доступ к управлению.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span>Товаров</span>
            <strong><?php echo (int)$totalProducts; ?></strong>
        </div>

        <div class="stat-card">
            <span>Категорий</span>
            <strong><?php echo (int)$totalCategories; ?></strong>
        </div>

        <div class="stat-card">
            <span>Пользователей</span>
            <strong><?php echo (int)$totalUsers; ?></strong>
        </div>

        <div class="stat-card">
            <span>Заказов</span>
            <strong><?php echo (int)$totalOrders; ?></strong>
        </div>

        <div class="stat-card">
            <span>Сумма заказов</span>
            <strong><?php echo number_format($totalSales, 2, '.', ' '); ?> ₽</strong>
        </div>
    </div>

    <div class="admin-dashboard">
        <section class="admin-box">
            <h2>Управление</h2>

            <div class="admin-links">
                <a href="/admin/products.php" class="btn">Товары</a>
                <a href="/admin/product_edit.php" class="btn">Добавить товар</a>
                <a href="/admin/categories.php" class="btn">Категории</a>
                <a href="/admin/orders_live.php" class="btn">Live-заказы</a>
            </div>
        </section>

        <section class="admin-box">
            <h2>Последние заказы</h2>

            <?php if (count($latestOrders) > 0): ?>
                <?php foreach ($latestOrders as $order): ?>
                    <div class="mini-order">
                        <div>
                            <strong>Заказ №<?php echo (int)$order['id']; ?></strong>
                            <p><?php echo e($order['user_name']); ?> — <?php echo e($order['created_at']); ?></p>
                        </div>

                        <span class="badge pink"><?php echo e($order['status']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">Заказов пока нет.</div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
