<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

csrf_check();

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

$allowedStatuses = array(
    'Новый',
    'В обработке',
    'Отправлен',
    'Завершён',
    'Отменён'
);

if ($order_id <= 0 || !in_array($status, $allowedStatuses)) {
    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE orders
    SET status = ?
    WHERE id = ?
");
$stmt->execute(array($status, $order_id));

echo 'ok';