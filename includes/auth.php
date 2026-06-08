<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuth()
{
    if (!isset($_SESSION['user'])) {
        return false;
    }

    global $pdo;
    static $checkedUserId = null;

    $userId = (int)$_SESSION['user']['id'];

    if (isset($pdo) && $pdo instanceof PDO && $checkedUserId !== $userId) {
        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE id = ? AND is_deleted = 0
        ");
        $stmt->execute(array($userId));

        if (!$stmt->fetch()) {
            unset($_SESSION['user']);
            return false;
        }

        $checkedUserId = $userId;
    }

    return true;
}

function isAdmin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function requireAuth()
{
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin()
{
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }

    if (!isAdmin()) {
        header('Location: /');
        exit;
    }
}

function requireCustomer()
{
    if (!isAuth()) {
        header('Location: /login.php');
        exit;
    }

    if (isAdmin()) {
        header('Location: /admin/index.php');
        exit;
    }
}

function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function product_image_url($filename)
{
    $filename = trim((string)$filename);

    if ($filename === '') {
        return null;
    }

    $path = __DIR__ . '/../uploads/products/' . $filename;

    if (!is_file($path)) {
        return null;
    }

    return '/uploads/products/' . rawurlencode($filename);
}

function getDefaultHeaderLinks()
{
    $links = array(
        array('href' => '/', 'label' => 'Главная'),
        array('href' => '/catalog.php', 'label' => 'Каталог')
    );

    if (isAuth()) {
        if (!isAdmin()) {
            $links[] = array('href' => '/cart.php', 'label' => 'Корзина');
            $links[] = array('href' => '/orders.php', 'label' => 'Мои заказы');
            $links[] = array('href' => '/account.php', 'label' => 'Аккаунт');
        } else {
            $links[] = array('href' => '/admin/index.php', 'label' => 'Админка');
        }

        $links[] = array('href' => '/logout.php', 'label' => 'Выход');
    } else {
        $links[] = array('href' => '/login.php', 'label' => 'Вход');
        $links[] = array('href' => '/register.php', 'label' => 'Регистрация');
    }

    return $links;
}

function getAdminHeaderLinks()
{
    return array(
        array('href' => '/', 'label' => 'На сайт'),
        array('href' => '/admin/index.php', 'label' => 'Админка'),
        array('href' => '/admin/products.php', 'label' => 'Товары'),
        array('href' => '/admin/categories.php', 'label' => 'Категории'),
        array('href' => '/admin/orders_live.php', 'label' => 'Live-заказы'),
        array('href' => '/logout.php', 'label' => 'Выход')
    );
}

function getOrderStatuses()
{
    return array(
        'Новый',
        'В обработке',
        'Отправлен',
        'Завершён',
        'Отменён'
    );
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            die('Ошибка безопасности: неверный CSRF-токен');
        }
    }
}
