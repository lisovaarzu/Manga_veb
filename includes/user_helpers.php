<?php

function deactivateUserAccount($pdo, $userId)
{
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE id = ?
            FOR UPDATE
        ");
        $stmt->execute(array($userId));
        $user = $stmt->fetch();

        if (!$user || (int)$user['is_deleted'] === 1) {
            $pdo->rollBack();
            return array('success' => false, 'message' => 'Пользователь не найден.');
        }

        if ($user['role'] === 'admin') {
            $pdo->rollBack();
            return array('success' => false, 'message' => 'Аккаунт администратора удалить нельзя.');
        }

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM orders
            WHERE user_id = ? AND status IN ('В обработке', 'Отправлен')
        ");
        $stmt->execute(array($userId));

        if ((int)$stmt->fetchColumn() > 0) {
            $pdo->rollBack();
            return array(
                'success' => false,
                'message' => 'Сначала завершите или отмените активные заказы пользователя.'
            );
        }

        $stmt = $pdo->prepare("
            SELECT order_items.product_id, order_items.quantity
            FROM orders
            INNER JOIN order_items ON order_items.order_id = orders.id
            WHERE orders.user_id = ? AND orders.status = 'Новый'
        ");
        $stmt->execute(array($userId));
        $items = $stmt->fetchAll();

        $stmt = $pdo->prepare("
            UPDATE products
            SET stock = stock + ?
            WHERE id = ?
        ");

        foreach ($items as $item) {
            $stmt->execute(array($item['quantity'], $item['product_id']));
        }

        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = 'Отменён'
            WHERE user_id = ? AND status = 'Новый'
        ");
        $stmt->execute(array($userId));

        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute(array($userId));

        $deletedEmail = 'deleted_' . $userId . '_' . time() . '@mangashop.local';
        $deletedPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE users
            SET name = 'Удалённый пользователь',
                email = ?,
                password = ?,
                is_deleted = 1
            WHERE id = ?
        ");
        $stmt->execute(array($deletedEmail, $deletedPassword, $userId));

        $pdo->commit();

        return array('success' => true, 'message' => 'Аккаунт удалён.');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return array('success' => false, 'message' => 'Не удалось удалить аккаунт.');
    }
}
