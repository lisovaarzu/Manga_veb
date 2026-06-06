<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if (isAuth()) {
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === '' || $password === '') {
        $error = 'Введите email и пароль';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(array($email));
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
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
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход — MangaShop</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="header">
    <a href="/" class="logo">MangaShop</a>

    <nav class="nav">
        <a href="/">Главная</a>
        <a href="/catalog.php">Каталог</a>
        <a href="/register.php">Регистрация</a>
    </nav>
</header>

<main class="form-page">
    <div class="form-box">
        <h1>Вход</h1>
        <p>Войди в аккаунт, чтобы управлять корзиной и заказами.</p>

        <?php if ($error): ?>
            <div class="alert error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post">
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

</body>
</html>