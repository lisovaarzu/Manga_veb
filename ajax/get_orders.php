<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$stmt = $pdo->query("
    SELECT orders.*, users.name AS user_name, users.email AS user_email
    FROM orders
    INNER JOIN users ON orders.user_id = users.id
    ORDER BY orders.id DESC
");
$orders = $stmt->fetchAll();

$statuses = getOrderStatuses();

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

if (count($orders) === 0) {
    echo '<div class="empty">Заказов пока нет.</div>';
    exit;
}
?>

<div class="orders-list">
    <?php foreach ($orders as $order): ?>
        <div class="order-card live-order-card">
            <div class="order-head">
                <div>
                    <h3>Заказ №<?php echo (int)$order['id']; ?></h3>
                    <p>
                        <?php echo e($order['created_at']); ?>
                        <br>
                        Клиент: <?php echo e($order['user_name']); ?>,
                        <?php echo e($order['user_email']); ?>
                    </p>
                </div>

                <div class="live-status-box">
                    <label>Статус</label>
                    <select class="form-control order-status-select" data-order-id="<?php echo (int)$order['id']; ?>">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo e($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                <?php echo e($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
