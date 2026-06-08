<?php

function changeOrderStatus($pdo, $orderId, $newStatus, $userId = null, $customerCancel = false)
{
    if (!in_array($newStatus, getOrderStatuses(), true)) {
        return array('success' => false, 'message' => 'Недопустимый статус заказа.');
    }

    try {
        $pdo->beginTransaction();

        $sql = "SELECT * FROM orders WHERE id = ?";
        $params = array($orderId);

        if ($userId !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }

        $sql .= " FOR UPDATE";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch();

        if (!$order) {
            $pdo->rollBack();
            return array('success' => false, 'message' => 'Заказ не найден.');
        }

        if ($customerCancel && $order['status'] !== 'Новый') {
            $pdo->rollBack();
            return array('success' => false, 'message' => 'Можно отменить только новый заказ.');
        }

        if ($order['status'] === $newStatus) {
            $pdo->commit();
            return array('success' => true, 'message' => 'Статус заказа не изменился.');
        }

        $stmt = $pdo->prepare("
            SELECT product_id, quantity
            FROM order_items
            WHERE order_id = ?
        ");
        $stmt->execute(array($orderId));
        $items = $stmt->fetchAll();

        if ($newStatus === 'Отменён' && $order['status'] !== 'Отменён') {
            $stmt = $pdo->prepare("
                UPDATE products
                SET stock = stock + ?
                WHERE id = ?
            ");

            foreach ($items as $item) {
                $stmt->execute(array($item['quantity'], $item['product_id']));
            }
        }

        if ($order['status'] === 'Отменён' && $newStatus !== 'Отменён') {
            $stmt = $pdo->prepare("
                UPDATE products
                SET stock = stock - ?
                WHERE id = ? AND stock >= ?
            ");

            foreach ($items as $item) {
                $stmt->execute(array(
                    $item['quantity'],
                    $item['product_id'],
                    $item['quantity']
                ));

                if ($stmt->rowCount() !== 1) {
                    $pdo->rollBack();
                    return array(
                        'success' => false,
                        'message' => 'Недостаточно товара на складе для восстановления заказа.'
                    );
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute(array($newStatus, $orderId));

        $pdo->commit();

        return array('success' => true, 'message' => 'Статус заказа обновлён.');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return array('success' => false, 'message' => 'Не удалось изменить статус заказа.');
    }
}
