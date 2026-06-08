<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/order_helpers.php';

requireCustomer();

$user_id = $_SESSION['user']['id'];
$error = '';
$cancelSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $result = changeOrderStatus($pdo, $orderId, 'Отменён', $user_id, true);

    if ($result['success']) {
        header('Location: /orders.php?cancelled=1');
        exit;
    }

    $error = $result['message'];
}

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
    $orderIds = array();

    foreach ($orders as $order) {
        $orderIds[] = (int)$order['id'];
        $orderItems[$order['id']] = array();
    }

    $placeholders = implode(', ', array_fill(0, count($orderIds), '?'));

    $stmt = $pdo->prepare("
        SELECT *
        FROM order_items
        WHERE order_id IN ($placeholders)
        ORDER BY order_id DESC, id ASC
    ");
    $stmt->execute($orderIds);

    foreach ($stmt->fetchAll() as $item) {
        $orderItems[$item['order_id']][] = $item;
    }
}

$success = isset($_GET['success']) ? (int)$_GET['success'] : 0;
$cancelSuccess = isset($_GET['cancelled']) && (int)$_GET['cancelled'] === 1;

$pageTitle = 'Мои заказы — MangaShop';
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

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

    <?php if ($cancelSuccess): ?>
        <div class="alert success">Заказ отменён, товары возвращены на склад.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error"><?php echo e($error); ?></div>
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

                    <?php if ($order['status'] === 'Новый'): ?>
                        <div class="order-actions">
                            <form method="post" onsubmit="return confirm('Отменить заказ и вернуть товары на склад?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                <button class="btn small btn-danger" type="submit">Отменить заказ</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            У тебя пока нет заказов.
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
