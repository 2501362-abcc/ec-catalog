<?php
/**
 * includes/config.php
 * .env を読み込み、PDO接続を確立する共通ファイル。
 * 全ページの先頭で require_once __DIR__ . '/../../includes/config.php'; として読み込む。
 */

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($name)] = trim($value);
    }
}

loadEnv(__DIR__ . '/../.env');

$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'ec_shop';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // 第23回で本格的なエラーハンドリング・ログ設計を扱うまでは、
    // 開発中のみの簡易表示にとどめる（本番でこのまま使わないこと）
    die('データベース接続に失敗しました。設定(.env)を確認してください。');
}
