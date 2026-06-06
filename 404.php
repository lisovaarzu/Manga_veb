<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

http_response_code(404);

$pageTitle = 'Страница не найдена — MangaShop';
require_once __DIR__ . '/includes/header.php';
?>

<main class="error-page">
    <div class="error-box">
        <span class="error-code">404</span>
        <h1>Страница не найдена</h1>
        <p>Возможно, товар был удалён или адрес указан неправильно.</p>

        <div class="hero-actions">
            <a href="/" class="btn">На главную</a>
            <a href="/catalog.php" class="btn btn-secondary">В каталог</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>