<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

$categories = $pdo->query("SELECT * FROM categories ORDER BY title ASC")->fetchAll();

$product = array(
    'id' => 0,
    'category_id' => '',
    'title' => '',
    'author' => '',
    'description' => '',
    'age_rating' => '16+',
    'price' => '',
    'image' => '',
    'stock' => ''
);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute(array($id));
    $foundProduct = $stmt->fetch();

    if (!$foundProduct) {
        require_once __DIR__ . '/../404.php';
        exit;
    }

    $product = $foundProduct;

    if (!isset($product['age_rating']) || $product['age_rating'] === '') {
        $product['age_rating'] = '16+';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $ageRatings = getAgeRatings();
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $ageRating = isset($_POST['age_rating']) ? trim($_POST['age_rating']) : '16+';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $imageName = $product['image'];

    if ($category_id <= 0) {
        $error = 'Выберите категорию';
    } elseif ($title === '') {
        $error = 'Введите название товара';
    } elseif (!in_array($ageRating, $ageRatings, true)) {
        $error = 'Выберите возрастной рейтинг';
    } elseif ($price <= 0) {
        $error = 'Введите корректную цену';
    } elseif ($stock < 0) {
        $error = 'Остаток не может быть отрицательным';
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Ошибка загрузки изображения';
            } else {
                $allowedTypes = array(
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp'
                );

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileType = $finfo ? finfo_file($finfo, $_FILES['image']['tmp_name']) : false;

                if ($finfo) {
                    finfo_close($finfo);
                }

                if (!$fileType || !isset($allowedTypes[$fileType])) {
                    $error = 'Можно загружать только JPG, PNG, GIF или WEBP';
                } else {
                    $extension = $allowedTypes[$fileType];
                    $newName = 'product_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;

                    $uploadDir = __DIR__ . '/../uploads/products/';
                    $uploadPath = $uploadDir . $newName;

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        if (!empty($imageName) && file_exists($uploadDir . $imageName)) {
                            unlink($uploadDir . $imageName);
                        }

                        $imageName = $newName;
                    } else {
                        $error = 'Не удалось сохранить изображение';
                    }
                }
            }
        }
    }

    if ($error === '') {
        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE products
                SET category_id = ?, title = ?, author = ?, description = ?, age_rating = ?, price = ?, image = ?, stock = ?
                WHERE id = ?
            ");

            $stmt->execute(array(
                $category_id,
                $title,
                $author,
                $description,
                $ageRating,
                $price,
                $imageName,
                $stock,
                $id
            ));
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO products (category_id, title, author, description, age_rating, price, image, stock)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute(array(
                $category_id,
                $title,
                $author,
                $description,
                $ageRating,
                $price,
                $imageName,
                $stock
            ));
        }

        header('Location: /admin/products.php');
        exit;
    }

    $product['category_id'] = $category_id;
    $product['title'] = $title;
    $product['author'] = $author;
    $product['description'] = $description;
    $product['age_rating'] = $ageRating;
    $product['price'] = $price;
    $product['stock'] = $stock;
    $product['image'] = $imageName;
}

$pageTitle = $id > 0 ? 'Редактировать товар — MangaShop' : 'Добавить товар — MangaShop';
$headerLinks = getAdminHeaderLinks();
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <div class="page-title">
        <div>
            <h1><?php echo $id > 0 ? 'Редактировать товар' : 'Добавить товар'; ?></h1>
            <p class="page-subtitle">Заполни карточку товара и добавь обложку манги.</p>
        </div>

        <a href="/admin/products.php" class="btn btn-secondary">Назад</a>
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?php echo csrf_field(); ?>

        <div class="admin-form-grid">
            <div class="admin-box">
                <div class="form-group">
                    <label>Категория</label>
                    <select class="form-control" name="category_id">
                        <option value="">Выберите категорию</option>

                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo (int)$category['id']; ?>" <?php echo (int)$product['category_id'] === (int)$category['id'] ? 'selected' : ''; ?>>
                                <?php echo e($category['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Название</label>
                    <input class="form-control" type="text" name="title" value="<?php echo e($product['title']); ?>">
                </div>

                <div class="form-group">
                    <label>Автор</label>
                    <input class="form-control" type="text" name="author" value="<?php echo e($product['author']); ?>">
                </div>

                <div class="form-group">
                    <label>Описание</label>
                    <textarea class="form-control" name="description"><?php echo e($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Возрастной рейтинг</label>
                    <select class="form-control" name="age_rating">
                        <?php foreach (getAgeRatings() as $rating): ?>
                            <option value="<?php echo e($rating); ?>" <?php echo $product['age_rating'] === $rating ? 'selected' : ''; ?>>
                                <?php echo e($rating); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Цена</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="price" value="<?php echo e($product['price']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Остаток</label>
                        <input class="form-control" type="number" min="0" name="stock" value="<?php echo e($product['stock']); ?>">
                    </div>
                </div>

                <button class="btn" type="submit">Сохранить</button>
            </div>

            <div class="admin-box">
                <h2>Обложка</h2>

                <?php $imageUrl = product_image_url($product['image']); ?>
                <?php if ($imageUrl): ?>
                    <img class="admin-preview" src="<?php echo e($imageUrl); ?>" alt="">
                <?php else: ?>
                    <div class="empty">Обложка ещё не загружена.</div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Загрузить новую обложку</label>
                    <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                </div>

                <p class="page-subtitle">Разрешены JPG, PNG, GIF и WEBP.</p>
            </div>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
