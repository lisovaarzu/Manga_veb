<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/user_helpers.php';

requireCustomer();

$userId = (int)$_SESSION['user']['id'];
$error = '';

$stmt = $pdo->prepare("
    SELECT id, name, email, password, created_at
    FROM users
    WHERE id = ? AND is_deleted = 0
");
$stmt->execute(array($userId));
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($password === '') {
        $error = 'Введите текущий пароль.';
    } elseif (!password_verify($password, $user['password'])) {
        $error = 'Неверный пароль.';
    } else {
        $result = deactivateUserAccount($pdo, $userId);

        if ($result['success']) {
            session_unset();
            session_destroy();
            header('Location: /login.php?account_deleted=1');
            exit;
        }

        $error = $result['message'];
    }
}

$pageTitle = 'Аккаунт — MangaShop';
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Мой аккаунт</h1>
            <p class="page-subtitle">Данные профиля и управление аккаунтом.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="account-grid">
        <section class="admin-box">
            <h2>Данные профиля</h2>
            <p><strong>Имя:</strong> <?php echo e($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo e($user['email']); ?></p>
            <p><strong>Дата регистрации:</strong> <?php echo e($user['created_at']); ?></p>
        </section>

        <section class="admin-box danger-zone">
            <h2>Удаление аккаунта</h2>
            <p>
                Корзина будет очищена, новые заказы отменятся, а вход в аккаунт станет невозможен.
                Завершённые заказы останутся в обезличенной истории магазина.
                Аккаунт с заказом в обработке или отправленным заказом удалить нельзя.
            </p>

            <form method="post" onsubmit="return confirm('Удалить аккаунт? Это действие нельзя отменить.');">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label>Текущий пароль</label>
                    <input class="form-control" type="password" name="password" required>
                </div>

                <button class="btn btn-danger" type="submit">Удалить аккаунт</button>
            </form>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
