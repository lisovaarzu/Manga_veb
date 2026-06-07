<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

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

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($name === '' || $email === '' || $password === '' || $password_confirm === '') {
        $error = 'Заполните все поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть минимум 6 символов';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute(array($email));
        $user = $stmt->fetch();

        if ($user) {
            $error = 'Пользователь с таким email уже существует';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, 'user')
            ");
            $stmt->execute(array($name, $email, $hash));

            $success = 'Регистрация прошла успешно. Теперь можно войти.';
        }
    }
}

$pageTitle = 'Регистрация — MangaShop';
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="form-page">
    <div class="form-box">
        <h1>Регистрация</h1>
        <p>Создай аккаунт, чтобы добавлять мангу в корзину и оформлять заказы.</p>

        <?php if ($error): ?>
            <div class="alert error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <form method="post">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label>Имя</label>
                <input class="form-control" type="text" name="name" value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input class="form-control" type="email" name="email" value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Пароль</label>
                <input class="form-control" type="password" name="password">
            </div>

            <div class="form-group">
                <label>Повторите пароль</label>
                <input class="form-control" type="password" name="password_confirm">
            </div>

            <button class="btn full-btn" type="submit">Зарегистрироваться</button>
        </form>

        <p>Уже есть аккаунт? <a href="/login.php" class="form-link">Войти</a></p>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
