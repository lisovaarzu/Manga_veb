<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/order_helpers.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

csrf_check();

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

$allowedStatuses = getOrderStatuses();

if ($order_id <= 0 || !in_array($status, $allowedStatuses)) {
    http_response_code(400);
    exit;
}

$result = changeOrderStatus($pdo, $order_id, $status);

if (!$result['success']) {
    http_response_code(409);
    echo $result['message'];
    exit;
}

echo $result['message'];
