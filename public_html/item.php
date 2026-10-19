<?php
/**
 * item.php ― 商品詳細ページ
 */
require_once __DIR__ . '/../includes/config.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM items WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    echo '商品が見つかりませんでした。<a href="items.php">一覧に戻る</a>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item['name']) ?> ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <p><a href="items.php">← 商品一覧に戻る</a></p>
        <img src="<?= $item['image'] ? 'uploads/items/' . htmlspecialchars($item['image']) : 'https://placehold.co/400x260' ?>"
             alt="" style="max-width:100%; border-radius:8px;">
        <h1><?= htmlspecialchars($item['name']) ?></h1>
        <p class="price">¥<?= number_format($item['price']) ?></p>
        <p><?= nl2br(htmlspecialchars($item['description'] ?? '')) ?></p>
        <p>在庫：<?= (int) $item['stock'] ?>点</p>

        <!--
            「カートに入れる」ボタンは第14回でカート機能を実装するまでは
            仮のリンクです。ログイン機能（第7〜9回）も未実装のため、
            ここでは表示のみにとどめています。
        -->
        <button disabled>カートに入れる（第14回で実装予定）</button>
    </main>
</body>
</html>
