<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

requireCustomer();

$user_id = $_SESSION['user']['id'];
$error = '';

$stmt = $pdo->prepare("
    SELECT 
        cart_items.id AS cart_id,
        cart_items.quantity,
        products.id AS product_id,
        products.title,
        products.price,
        products.stock
    FROM cart_items
    INNER JOIN products ON cart_items.product_id = products.id
    WHERE cart_items.user_id = ?
");
$stmt->execute(array($user_id));
$items = $stmt->fetchAll();

if (count($items) === 0) {
    header('Location: /cart.php');
    exit;
}

$total = 0;

foreach ($items as $item) {
    if ($item['quantity'] > $item['stock']) {
        $error = 'Количество товара "' . $item['title'] . '" больше, чем есть на складе.';
    }

    $total += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    csrf_check();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT
                cart_items.id AS cart_id,
                cart_items.quantity,
                products.id AS product_id,
                products.title,
                products.price,
                products.stock
            FROM cart_items
            INNER JOIN products ON cart_items.product_id = products.id
            WHERE cart_items.user_id = ?
            FOR UPDATE
        ");
        $stmt->execute(array($user_id));
        $items = $stmt->fetchAll();

        if (count($items) === 0) {
            $pdo->rollBack();
            header('Location: /cart.php');
            exit;
        }

        $total = 0;
        $stockChanged = false;

        foreach ($items as $item) {
            if ($item['stock'] < $item['quantity']) {
                $stockChanged = true;
                break;
            }

            $total += $item['price'] * $item['quantity'];
        }

        if (!$stockChanged) {
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_price, status)
                VALUES (?, ?, 'Новый')
            ");
            $stmt->execute(array($user_id, $total));

            $order_id = $pdo->lastInsertId();

            foreach ($items as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, title, price, quantity)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute(array(
                    $order_id,
                    $item['product_id'],
                    $item['title'],
                    $item['price'],
                    $item['quantity']
                ));

                $stmt = $pdo->prepare("
                    UPDATE products
                    SET stock = stock - ?
                    WHERE id = ? AND stock >= ?
                ");
                $stmt->execute(array($item['quantity'], $item['product_id'], $item['quantity']));

                if ($stmt->rowCount() !== 1) {
                    $stockChanged = true;
                    break;
                }
            }
        }

        if ($stockChanged) {
            $pdo->rollBack();
            $error = 'Пока вы оформляли заказ, остатки изменились. Проверьте корзину ещё раз.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute(array($user_id));

            $pdo->commit();

            header('Location: /orders.php?success=1');
            exit;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $error = 'Ошибка оформления заказа. Попробуйте ещё раз.';
    }
}

$pageTitle = 'Оформление заказа — MangaShop';
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Оформление заказа</h1>
            <p class="page-subtitle">Это тестовая покупка. Оплата не требуется.</p>
        </div>

        <a href="/cart.php" class="btn btn-secondary">Назад в корзину</a>
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="checkout-layout">
        <div class="checkout-box">
            <h2>Состав заказа</h2>

            <?php foreach ($items as $item): ?>
                <div class="checkout-item">
                    <div>
                        <strong><?php echo e($item['title']); ?></strong>
                        <p><?php echo (int)$item['quantity']; ?> × <?php echo e($item['price']); ?> ₽</p>
                    </div>

                    <b><?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> ₽</b>
                </div>
            <?php endforeach; ?>
        </div>

        <aside class="cart-summary">
            <h2>Итого</h2>

            <div class="summary-row">
                <span>Товаров:</span>
                <strong><?php echo count($items); ?></strong>
            </div>

            <div class="summary-row">
                <span>К оплате:</span>
                <strong><?php echo number_format($total, 2, '.', ' '); ?> ₽</strong>
            </div>

            <form method="post">
                <?php echo csrf_field(); ?>
                <button class="btn full-btn" type="submit">Подтвердить заказ</button>
            </form>
        </aside>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
