<?php
/**
 * items.php ― 商品一覧・キーワード検索・カテゴリ絞り込み・ページネーション
 *
 * 初級：一覧表示 → すでに動きます
 * 中級：LIKE検索・カテゴリ絞り込み・0件時の表示 → TODOを埋めてください
 * 上級：検索条件を保ったままのページネーション → TODOを埋めてください
 */
require_once __DIR__ . '/../includes/config.php';

// ---- ① 検索条件の取得 ----------------------------------------------------
$keyword  = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 9;

// ---- ② WHERE句を動的に組み立てる（必ずプレースホルダを使うこと）------------
// TODO（中級）: $keyword が空でなければ "name LIKE :keyword" 相当の条件を追加する
//   ヒント：LIKE検索では % と _ がワイルドカードとして特別な意味を持つため、
//   ユーザー入力にこれらの文字が含まれていた場合はエスケープしてから渡すこと。
// TODO（中級）: $category が空でなければ "category = :category" 相当の条件を追加する
$conditions = [];
$params     = [];

if ($keyword !== '') {
    // LIKE検索では % と _ が特別な意味を持つため、エスケープしてから渡す
    $escapedKeyword = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $keyword);
    $conditions[]   = "name LIKE :keyword ESCAPE '\\\\'";
    $params['keyword'] = '%' . $escapedKeyword . '%';
}

if ($category !== '') {
    $conditions[] = 'category = :category';
    $params['category'] = $category;
}

$whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// ---- ③ 該当件数を取得し、総ページ数を計算する（上級）------------------------
// TODO: COUNT(*) で件数を取得し、$perPage で割って総ページ数を求める
// TODO: $page が総ページ数を超えていたら、総ページ数に丸める（末尾ページ対策）
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM items {$whereSql}");
$countStmt->execute($params);
$totalCount = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalCount / $perPage));
$page       = min($page, $totalPages); // 存在しない末尾ページへの飛び越し対策
$offset     = ($page - 1) * $perPage;

// ---- ④ 商品一覧を取得する（初級）--------------------------------------------
$sql = "SELECT id, name, price, image, stock, category
        FROM items
        {$whereSql}
        ORDER BY created_at DESC
        LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$categories = $pdo->query('SELECT DISTINCT category FROM items WHERE category IS NOT NULL ORDER BY category')
                   ->fetchAll(PDO::FETCH_COLUMN);

// TODO（上級）: ページ送りリンクで検索条件を保持するためのクエリ文字列を組み立てる関数を作る
function buildQuery(array $overrides = []): string
{
    $base = ['keyword' => $_GET['keyword'] ?? '', 'category' => $_GET['category'] ?? '', 'page' => $_GET['page'] ?? 1];
    return http_build_query(array_merge($base, $overrides));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品一覧 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>商品一覧</h1>

        <form class="search-form" method="get" action="items.php">
            <input type="text" name="keyword" placeholder="商品名で検索"
                   value="<?= htmlspecialchars($keyword) ?>">
            <select name="category">
                <option value="">すべてのカテゴリ</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= $c === $category ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">検索</button>
        </form>

        <?php if (empty($items)): ?>
            <!-- TODO（中級）: 該当0件の時に、ユーザーに分かりやすいメッセージを表示する -->
            <p class="empty-state">該当する商品が見つかりませんでした。</p>
        <?php else: ?>
            <div class="item-grid">
                <?php foreach ($items as $item): ?>
                    <a class="item-card" href="item.php?id=<?= (int) $item['id'] ?>" style="text-decoration:none; color:inherit;">
                        <img src="<?= $item['image'] ? 'uploads/items/' . htmlspecialchars($item['image']) : 'https://placehold.co/220x140' ?>" alt="">
                        <div class="name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="price">¥<?= number_format($item['price']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- TODO（上級）: ページネーションのUIを作る（前へ／各ページ番号／次へ）。
                 buildQuery() を使って検索条件を保ったままリンクを組み立てること -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="items.php?<?= buildQuery(['page' => $page - 1]) ?>">‹ 前へ</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p === $page): ?>
                        <span class="current"><?= $p ?></span>
                    <?php else: ?>
                        <a href="items.php?<?= buildQuery(['page' => $p]) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="items.php?<?= buildQuery(['page' => $page + 1]) ?>">次へ ›</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
