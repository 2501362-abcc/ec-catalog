<?php
/**
 * admin/item-add.php ― 商品登録（初級：テキスト項目のCRUD／中級：画像アップロード）
 */
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/upload_helper.php';

$errors = [];
$name = $price = $description = $stock = $category = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO（初級）: $_POST から name, price, description, stock, category を取得する（trim すること）
    $name        = trim($_POST['name'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stock       = trim($_POST['stock'] ?? '');
    $category    = trim($_POST['category'] ?? '');

    if ($name === '')           $errors[] = '商品名を入力してください';
    if (!ctype_digit($price))   $errors[] = '価格は0以上の整数で入力してください';
    if (!ctype_digit($stock))   $errors[] = '在庫数は0以上の整数で入力してください';

    // TODO（初級）: 入力内容の基本チェック
    //   name が空、price/stock が数字でない場合はそれぞれ $errors[] にメッセージを追加する

    $filename = null;
    if (empty($errors)) {
        $uploadDir = __DIR__ . '/../uploads/items';
        $result = handleImageUpload($_FILES['image'] ?? [], $uploadDir);
        if (!$result['ok']) {
            $errors[] = $result['error'];
        } else {
            $filename = $result['filename'];
        }
    }

    if (empty($errors)) {
        // TODO（初級）: items テーブルへ INSERT する（必ずプレースホルダを使うこと）
        $stmt = $pdo->prepare(
            'INSERT INTO items (name, price, description, image, stock, category) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, (int) $price, $description, $filename, (int) $stock, $category ?: null]);

        header('Location: item-list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品登録 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><a href="../index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>商品登録</h1>

        <?php if ($errors): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <p><label>商品名<br><input type="text" name="name" value="<?= htmlspecialchars($name) ?>"></label></p>
            <p><label>価格（円）<br><input type="number" name="price" value="<?= htmlspecialchars($price) ?>"></label></p>
            <p><label>在庫数<br><input type="number" name="stock" value="<?= htmlspecialchars($stock) ?>"></label></p>
            <p><label>カテゴリ<br><input type="text" name="category" value="<?= htmlspecialchars($category) ?>"></label></p>
            <p><label>説明<br><textarea name="description" rows="4" cols="40"><?= htmlspecialchars($description) ?></textarea></label></p>
            <p><label>商品画像（任意・2MB以内・jpg/png/gif/webp）<br><input type="file" name="image" accept="image/*"></label></p>
            <button type="submit">登録する</button>
        </form>
    </main>
</body>
</html>
