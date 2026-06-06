<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;

    if ($action === 'update') {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        if ($quantity < 1) {
            $quantity = 1;
        }

        $stmt = $pdo->prepare("
            SELECT cart_items.*, products.stock
            FROM cart_items
            INNER JOIN products ON cart_items.product_id = products.id
            WHERE cart_items.id = ? AND cart_items.user_id = ?
        ");
        $stmt->execute(array($cart_id, $user_id));
        $item = $stmt->fetch();

        if ($item) {
            if ($quantity > $item['stock']) {
                $quantity = $item['stock'];
            }

            $stmt = $pdo->prepare("
                UPDATE cart_items
                SET quantity = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute(array($quantity, $cart_id, $user_id));
        }
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("
            DELETE FROM cart_items
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute(array($cart_id, $user_id));
    }

    header('Location: /cart.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        cart_items.id AS cart_id,
        cart_items.quantity,
        products.id AS product_id,
        products.title,
        products.author,
        products.price,
        products.image,
        products.stock
    FROM cart_items
    INNER JOIN products ON cart_items.product_id = products.id
    WHERE cart_items.user_id = ?
    ORDER BY cart_items.id DESC
");
$stmt->execute(array($user_id));
$items = $stmt->fetchAll();

$total = 0;

foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина — MangaShop</title>
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
            <h1>Корзина</h1>
            <p class="page-subtitle">Проверь товары перед оформлением тестового заказа.</p>
        </div>

        <a href="/catalog.php" class="btn btn-secondary">Продолжить покупки</a>
    </div>

    <?php if (count($items) > 0): ?>
        <div class="cart-layout">
            <div class="cart-list">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item">
                        <div class="cart-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="/uploads/products/<?php echo e($item['image']); ?>" alt="<?php echo e($item['title']); ?>">
                            <?php else: ?>
                                <span>Нет изображения</span>
                            <?php endif; ?>
                        </div>

                        <div class="cart-info">
                            <h3>
                                <a href="/product.php?id=<?php echo $item['product_id']; ?>">
                                    <?php echo e($item['title']); ?>
                                </a>
                            </h3>

                            <p><?php echo e($item['author']); ?></p>
                            <span class="badge pink">В наличии: <?php echo (int)$item['stock']; ?></span>

                            <div class="cart-price">
                                <?php echo e($item['price']); ?> ₽
                            </div>
                        </div>

                        <div class="cart-controls">
                            <form method="post" class="cart-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">

                                <label>Кол-во</label>
                                <input 
                                    class="form-control" 
                                    type="number" 
                                    name="quantity" 
                                    value="<?php echo (int)$item['quantity']; ?>" 
                                    min="1" 
                                    max="<?php echo (int)$item['stock']; ?>"
                                >

                                <button class="btn small" type="submit">Обновить</button>
                            </form>

                            <form method="post">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <button class="btn small btn-danger" type="submit">Удалить</button>
                            </form>
                        </div>
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
                    <span>Сумма:</span>
                    <strong><?php echo number_format($total, 2, '.', ' '); ?> ₽</strong>
                </div>

                <a href="/checkout.php" class="btn full-btn">Оформить заказ</a>
            </aside>
        </div>
    <?php else: ?>
        <div class="empty">
            Корзина пока пустая. Перейди в каталог и добавь мангу.
        </div>
    <?php endif; ?>
</main>

</body>
</html>