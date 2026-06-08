<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user_helpers.php';

requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $result = deactivateUserAccount($pdo, $userId);

    if ($result['success']) {
        $success = 'Аккаунт пользователя удалён.';
    } else {
        $error = $result['message'];
    }
}

$users = $pdo->query("
    SELECT
        users.id,
        users.name,
        users.email,
        users.created_at,
        COUNT(orders.id) AS orders_count
    FROM users
    LEFT JOIN orders ON orders.user_id = users.id
    WHERE users.role = 'user' AND users.is_deleted = 0
    GROUP BY users.id, users.name, users.email, users.created_at
    ORDER BY users.id DESC
")->fetchAll();

$pageTitle = 'Пользователи — Админка MangaShop';
$headerLinks = getAdminHeaderLinks();
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Пользователи</h1>
            <p class="page-subtitle">
                Просмотр и удаление обычных аккаунтов. Пользователя с заказом в обработке
                или отправленным заказом сначала нужно обслужить либо отменить его заказ.
            </p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?php echo e($success); ?></div>
    <?php endif; ?>

    <?php if (count($users) > 0): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Заказов</th>
                        <th>Дата регистрации</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo (int)$user['id']; ?></td>
                            <td><?php echo e($user['name']); ?></td>
                            <td><?php echo e($user['email']); ?></td>
                            <td><?php echo (int)$user['orders_count']; ?></td>
                            <td><?php echo e($user['created_at']); ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Удалить аккаунт пользователя?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                    <button class="btn small btn-danger" type="submit">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty">Активных пользователей пока нет.</div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
