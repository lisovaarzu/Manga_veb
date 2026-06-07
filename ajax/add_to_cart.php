<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireCustomer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /catalog.php');
    exit;
}

csrf_check();

$user_id = $_SESSION['user']['id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($quantity < 1) {
    $quantity = 1;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute(array($product_id));
$product = $stmt->fetch();

if (!$product || $product['stock'] <= 0) {
    header('Location: /catalog.php');
    exit;
}

if ($quantity > $product['stock']) {
    $quantity = $product['stock'];
}

$stmt = $pdo->prepare("
    SELECT *
    FROM cart_items
    WHERE user_id = ? AND product_id = ?
");
$stmt->execute(array($user_id, $product_id));
$cartItem = $stmt->fetch();

if ($cartItem) {
    $newQuantity = $cartItem['quantity'] + $quantity;

    if ($newQuantity > $product['stock']) {
        $newQuantity = $product['stock'];
    }

    $stmt = $pdo->prepare("
        UPDATE cart_items
        SET quantity = ?
        WHERE id = ?
    ");
    $stmt->execute(array($newQuantity, $cartItem['id']));
} else {
    $stmt = $pdo->prepare("
        INSERT INTO cart_items (user_id, product_id, quantity)
        VALUES (?, ?, ?)
    ");
    $stmt->execute(array($user_id, $product_id, $quantity));
}

header('Location: /cart.php');
exit;
