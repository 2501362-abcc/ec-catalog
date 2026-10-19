<?php
/**
 * includes/upload_helper.php
 *
 * 画像アップロードの安全な受け取り方（第3回・上級）。
 *
 * 「.php」ファイルを画像と偽ってアップロードされ、拡張子チェックだけを
 * すり抜けてサーバー上で実行されてしまう（Webシェル設置）事故を防ぐため、
 * 以下の3段階でチェックすること。
 *   1. 拡張子のホワイトリストチェック
 *   2. finfo_file() による実体（MIMEタイプ）チェック（拡張子の偽装を見抜く）
 *   3. 保存ファイル名をランダム化（元のファイル名を一切信用しない）
 *
 * @return array{ok:bool, filename:?string, error:?string}
 */
function handleImageUpload(array $file, string $destDir): array
{
    $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize     = 2 * 1024 * 1024; // 2MB

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => null, 'error' => 'アップロードに失敗しました'];
    }

    // ファイルサイズチェック
    if ($file['size'] > $maxSize) {
        return ['ok' => false, 'filename' => null, 'error' => '画像は2MB以内にしてください'];
    }

    // 拡張子チェック
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        return ['ok' => false, 'filename' => null, 'error' => '対応していない画像形式です'];
    }

    // MIMEタイプチェック
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMime, true)) {
        return ['ok' => false, 'filename' => null, 'error' => '画像ファイルとして認識できません'];
    }

    // 保存先フォルダがなければ作成
    if (!is_dir($destDir)) {
        mkdir($destDir, 0777, true);
    }

    // ランダムなファイル名を作成
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;

    // ファイルを保存
    if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) {
        return ['ok' => false, 'filename' => null, 'error' => '画像の保存に失敗しました'];
    }

    return ['ok' => true, 'filename' => $filename, 'error' => null];
}