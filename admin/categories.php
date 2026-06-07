<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'create') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);

        if ($title === '') {
            $error = 'Введите название категории';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO categories (title, description)
                VALUES (?, ?)
            ");
            $stmt->execute(array($title, $description));
            $success = 'Категория добавлена';
        }
    }

    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);

        if ($title === '') {
            $error = 'Введите название категории';
        } else {
            $stmt = $pdo->prepare("
                UPDATE categories
                SET title = ?, description = ?
                WHERE id = ?
            ");
            $stmt->execute(array($title, $description, $id));
            $success = 'Категория обновлена';
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];

        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute(array($id));

        $success = 'Категория удалена';
    }
}

$categories = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
")->fetchAll();

$pageTitle = 'Категории — Админка MangaShop';
$headerLinks = getAdminHeaderLinks();
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1>Категории</h1>
            <p class="page-subtitle">Управление группами товаров: сёнэн, сэйнэн, романтика и другие.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?php echo e($success); ?></div>
    <?php endif; ?>

    <div class="admin-layout">
        <section class="admin-box">
            <h2>Добавить категорию</h2>

            <form method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label>Название</label>
                    <input class="form-control" type="text" name="title">
                </div>

                <div class="form-group">
                    <label>Описание</label>
                    <textarea class="form-control" name="description"></textarea>
                </div>

                <button class="btn full-btn" type="submit">Добавить</button>
            </form>
        </section>

        <section class="admin-box">
            <h2>Список категорий</h2>

            <?php if (count($categories) > 0): ?>
                <div class="admin-list">
                    <?php foreach ($categories as $category): ?>
                        <div class="admin-list-item">
                            <form method="post">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">

                                <div class="form-group">
                                    <label>Название</label>
                                    <input class="form-control" type="text" name="title" value="<?php echo e($category['title']); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Описание</label>
                                    <textarea class="form-control" name="description"><?php echo e($category['description']); ?></textarea>
                                </div>

                                <div class="actions">
                                    <button class="btn small" type="submit">Сохранить</button>
                                </div>
                            </form>

                            <form method="post" onsubmit="return confirm('Удалить категорию? Все товары этой категории тоже удалятся.');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                <button class="btn small btn-danger" type="submit">Удалить</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">Категорий пока нет.</div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
