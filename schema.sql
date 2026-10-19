-- ============================================================
-- schema.sql
-- 第2回 DB設計とER図：解答例（この回時点の最新版）
--
-- 以降のコマ（第8回・第19回・第22回など）で、この上にさらに
-- ALTER TABLE が加わっていきます。各回の solution/schema.sql は
-- その回終了時点の完全な状態を反映しています。
-- ============================================================

CREATE DATABASE IF NOT EXISTS ec_shop DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ec_shop;

-- ------------------------------------------------------------
-- users：会員情報
-- role : 'member'（一般会員）/ 'admin'（管理者）※第9回の権限分岐で使用
-- password : この時点ではまだ平文想定（第11回でハッシュ化に対応）
-- ------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(255) UNIQUE NOT NULL,
    password      VARCHAR(255) NOT NULL,
    name          VARCHAR(100) NOT NULL,
    address       VARCHAR(255),
    role          ENUM('member', 'admin') NOT NULL DEFAULT 'member',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- items：商品情報
-- ------------------------------------------------------------
CREATE TABLE items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255) NOT NULL,
    price         INT NOT NULL,
    description   TEXT,
    image         VARCHAR(255),
    stock         INT NOT NULL DEFAULT 0,
    category      VARCHAR(100),
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- cart：誰が・どの商品を・いくつカートに入れているか
-- 第14回：user_id + item_id の組み合わせで重複を防ぐ設計にする
-- ------------------------------------------------------------
CREATE TABLE cart (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    item_id       INT NOT NULL,
    quantity      INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- ------------------------------------------------------------
-- favorites：多対多の中間テーブル（第16回で複合主キーの意味を扱う）
-- ------------------------------------------------------------
CREATE TABLE favorites (
    user_id       INT NOT NULL,
    item_id       INT NOT NULL,
    PRIMARY KEY (user_id, item_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- ------------------------------------------------------------
-- orders：注文の本体（1回の注文につき1行）
-- status : 'pending'（未決済）/ 'paid'（決済済み）/ 'cancelled'（キャンセル） ※第19回で使用
-- ------------------------------------------------------------
CREATE TABLE orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    order_date    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_price   INT NOT NULL,
    status        ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ------------------------------------------------------------
-- order_details：注文の明細（1回の注文に複数商品が含まれる）
-- price は注文当時の単価をコピーして保持する（items.priceが後で変わっても、
-- 過去の注文金額が変わらないようにするため）
-- ------------------------------------------------------------
CREATE TABLE order_details (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL,
    item_id       INT NOT NULL,
    quantity      INT NOT NULL,
    price         INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);
