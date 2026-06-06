<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Live-заказы — MangaShop';
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Live-заказы</h1>
            <p class="page-subtitle">Список заказов обновляется автоматически каждые 3 секунды.</p>
        </div>
    </div>

    <div id="orders-live">
        <div class="empty">Загрузка заказов...</div>
    </div>
</main>

<script src="/assets/js/jquery.min.js"></script>
<script>
    var CSRF_TOKEN = '<?php echo e(csrf_token()); ?>';
</script>
<script src="/assets/js/admin.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>