<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if (isAuth()) {
    if (isAdmin()) {
        header('Location: /admin/index.php');
    } else {
        header('Location: /');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === '' || $password === '') {
        $error = 'Введите email и пароль';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(array($email));
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user'] = array(
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            );

            if ($user['role'] === 'admin') {
                header('Location: /admin/index.php');
            } else {
                header('Location: /');
            }
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}

$pageTitle = 'Вход — MangaShop';
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="form-page">
    <div class="form-box">
        <h1>Вход</h1>
        <p>Войди в аккаунт, чтобы управлять корзиной и заказами.</p>

        <?php if ($error): ?>
            <div class="alert error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label>Email</label>
                <input class="form-control" type="email" name="email" value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Пароль</label>
                <input class="form-control" type="password" name="password">
            </div>

            <button class="btn full-btn" type="submit">Войти</button>
        </form>

        <p>Нет аккаунта? <a href="/register.php" class="form-link">Зарегистрироваться</a></p>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
