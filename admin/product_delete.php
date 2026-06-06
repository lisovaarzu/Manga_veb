<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/products.php');
    exit;
}

csrf_check();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute(array($id));
$product = $stmt->fetch();

if ($product) {
    $uploadDir = __DIR__ . '/../uploads/products/';

    if (!empty($product['image']) && file_exists($uploadDir . $product['image'])) {
        unlink($uploadDir . $product['image']);
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute(array($id));
}

header('Location: /admin/products.php');
exit;