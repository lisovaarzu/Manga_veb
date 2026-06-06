<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
");
$stmt->execute(array($user_id));
$orders = $stmt->fetchAll();

$orderItems = array();

if (count($orders) > 0) {
    foreach ($orders as $order) {
        $stmt = $pdo->prepare("
            SELECT *
            FROM order_items
            WHERE order_id = ?
        ");
        $stmt->execute(array($order['id']));
        $orderItems[$order['id']] = $stmt->fetchAll();
    }
}

$success = isset($_GET['success']) ? (int)$_GET['success'] : 0;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заказы — MangaShop</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="header">
    <a href="/" class="logo">MangaShop</a>

    <nav class="nav">
        <a href="/">Главная</a>
        <a href="/catalog.php">Каталог</a>
        <a href="/cart.php">Корзина</a>
        <a href="/orders.php">Мои заказы</a>

        <?php if (isAdmin()): ?>
            <a href="/admin/index.php">Админка</a>
        <?php endif; ?>

        <a href="/logout.php">Выход</a>
    </nav>
</header>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Мои заказы</h1>
            <p class="page-subtitle">Здесь можно отслеживать статус заказов.</p>
        </div>

        <a href="/catalog.php" class="btn btn-secondary">В каталог</a>
    </div>

    <?php if ($success): ?>
        <div class="alert success">Заказ успешно оформлен.</div>
    <?php endif; ?>

    <?php if (count($orders) > 0): ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-head">
                        <div>
                            <h3>Заказ №<?php echo (int)$order['id']; ?></h3>
                            <p><?php echo e($order['created_at']); ?></p>
                        </div>

                        <span class="order-status">
                            <?php echo e($order['status']); ?>
                        </span>
                    </div>

                    <div class="order-items">
                        <?php foreach ($orderItems[$order['id']] as $item): ?>
                            <div class="order-item">
                                <span>
                                    <?php echo e($item['title']); ?>
                                    × <?php echo (int)$item['quantity']; ?>
                                </span>

                                <strong>
                                    <?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> ₽
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-total">
                        Итого:
                        <strong><?php echo number_format($order['total_price'], 2, '.', ' '); ?> ₽</strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            У тебя пока нет заказов.
        </div>
    <?php endif; ?>
</main>

</body>
</html>