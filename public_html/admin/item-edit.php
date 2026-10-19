<?php
/**
 * admin/item-edit.php ― 商品編集
 */
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/upload_helper.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM items WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    echo '商品が見つかりませんでした。<a href="item-list.php">一覧に戻る</a>';
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTから値を取得
    $name        = trim($_POST['name'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stock       = trim($_POST['stock'] ?? '');
    $category    = trim($_POST['category'] ?? '');

    // バリデーション
    if ($name === '') {
        $errors[] = '商品名を入力してください';
    }

    if (!ctype_digit($price)) {
        $errors[] = '価格は0以上の整数で入力してください';
    }

    if (!ctype_digit($stock)) {
        $errors[] = '在庫数は0以上の整数で入力してください';
    }

    // 未変更なら現在の画像をそのまま維持
    $filename = $item['image'];

    if (empty($errors)) {
        $uploadDir = __DIR__ . '/../uploads/items';
        $result = handleImageUpload($_FILES['image'] ?? [], $uploadDir);

        if (!$result['ok']) {
            $errors[] = $result['error'];
        } elseif ($result['filename'] !== null) {
            $filename = $result['filename'];
        }
    }

    if (empty($errors)) {
        // 商品情報を更新
        $stmt = $pdo->prepare(
            'UPDATE items SET name = ?, price = ?, description = ?, image = ?, stock = ?, category = ? WHERE id = ?'
        );

        $stmt->execute([
            $name,
            (int) $price,
            $description,
            $filename,
            (int) $stock,
            $category ?: null,
            $id
        ]);

        header('Location: item-list.php');
        exit;
    }

    $item = array_merge($item, compact('name', 'price', 'description', 'stock', 'category'));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品編集 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><a href="../index.php">Tsuchi-to-Hi</a></header>

    <main>
        <h1>商品編集</h1>

        <?php if ($errors): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">

            <p>
                <label>
                    商品名<br>
                    <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>">
                </label>
            </p>

            <p>
                <label>
                    価格（円）<br>
                    <input type="number" name="price" value="<?= htmlspecialchars($item['price']) ?>">
                </label>
            </p>

            <p>
                <label>
                    在庫数<br>
                    <input type="number" name="stock" value="<?= htmlspecialchars($item['stock']) ?>">
                </label>
            </p>

            <p>
                <label>
                    カテゴリ<br>
                    <input type="text" name="category" value="<?= htmlspecialchars($item['category'] ?? '') ?>">
                </label>
            </p>

            <p>
                <label>
                    説明<br>
                    <textarea name="description" rows="4" cols="40"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                </label>
            </p>

            <?php if ($item['image']): ?>
                <p>
                    <img src="../uploads/items/<?= htmlspecialchars($item['image']) ?>" style="max-width:160px;">
                </p>
            <?php endif; ?>

            <p>
                <label>
                    商品画像を変更（任意）<br>
                    <input type="file" name="image" accept="image/*">
                </label>
            </p>

            <button type="submit">更新する</button>
        </form>
    </main>
</body>
</html>
