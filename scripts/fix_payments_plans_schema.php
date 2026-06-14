<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
init_db();

$pdo = db();

function table_exists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

if (!function_exists('column_exists')) {
    function column_exists(PDO $pdo, string $table, string $column): bool {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch();
    }
}

function add_column(PDO $pdo, string $table, string $column, string $definition): void {
    if (!column_exists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        echo "added $table.$column\n";
    }
}

$pdo->exec("CREATE TABLE IF NOT EXISTS plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  days INT NOT NULL,
  price INT NOT NULL,
  old_price INT NULL,
  badge VARCHAR(100) NULL,
  description TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_popular TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 100,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

add_column($pdo, 'plans', 'old_price', 'INT NULL AFTER `price`');
add_column($pdo, 'plans', 'badge', 'VARCHAR(100) NULL AFTER `old_price`');
add_column($pdo, 'plans', 'description', 'TEXT NULL AFTER `badge`');
add_column($pdo, 'plans', 'is_active', 'TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`');
add_column($pdo, 'plans', 'is_popular', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`');
add_column($pdo, 'plans', 'sort_order', 'INT NOT NULL DEFAULT 100 AFTER `is_popular`');
add_column($pdo, 'plans', 'created_at', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `sort_order`');
add_column($pdo, 'plans', 'updated_at', 'DATETIME NULL AFTER `created_at`');

$pdo->exec("CREATE TABLE IF NOT EXISTS payment_providers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  title VARCHAR(190) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  config_json TEXT NULL,
  sort_order INT NOT NULL DEFAULT 100,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

add_column($pdo, 'payment_providers', 'title', 'VARCHAR(190) NOT NULL DEFAULT "" AFTER `code`');
add_column($pdo, 'payment_providers', 'is_active', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `title`');
add_column($pdo, 'payment_providers', 'config_json', 'TEXT NULL AFTER `is_active`');
add_column($pdo, 'payment_providers', 'sort_order', 'INT NOT NULL DEFAULT 100 AFTER `config_json`');
add_column($pdo, 'payment_providers', 'created_at', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `sort_order`');
add_column($pdo, 'payment_providers', 'updated_at', 'DATETIME NULL AFTER `created_at`');

if (column_exists($pdo, 'payment_providers', 'settings_json') && column_exists($pdo, 'payment_providers', 'config_json')) {
    $pdo->exec("UPDATE payment_providers SET config_json=settings_json WHERE (config_json IS NULL OR config_json='') AND settings_json IS NOT NULL AND settings_json<>''");
}

$pdo->exec("CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_id INT NULL,
  plan_id INT NULL,
  provider_code VARCHAR(64) NOT NULL,
  amount INT NOT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'RUB',
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  external_id VARCHAR(190) NULL,
  pay_url TEXT NULL,
  raw_response MEDIUMTEXT NULL,
  raw_request MEDIUMTEXT NULL,
  raw_json MEDIUMTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  updated_at DATETIME NULL,
  INDEX idx_payments_user_id (user_id),
  INDEX idx_payments_order_id (order_id),
  INDEX idx_payments_status (status),
  INDEX idx_payments_external_id (external_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

foreach ([
    'order_id' => 'INT NULL AFTER `user_id`',
    'plan_id' => 'INT NULL AFTER `order_id`',
    'provider_code' => 'VARCHAR(64) NOT NULL DEFAULT "manual" AFTER `plan_id`',
    'currency' => 'VARCHAR(10) NOT NULL DEFAULT "RUB" AFTER `amount`',
    'status' => 'VARCHAR(30) NOT NULL DEFAULT "pending" AFTER `currency`',
    'external_id' => 'VARCHAR(190) NULL AFTER `status`',
    'pay_url' => 'TEXT NULL AFTER `external_id`',
    'raw_response' => 'MEDIUMTEXT NULL AFTER `pay_url`',
    'raw_request' => 'MEDIUMTEXT NULL AFTER `raw_response`',
    'raw_json' => 'MEDIUMTEXT NULL AFTER `raw_request`',
    'paid_at' => 'DATETIME NULL AFTER `created_at`',
    'updated_at' => 'DATETIME NULL AFTER `paid_at`',
] as $col => $def) {
    add_column($pdo, 'payments', $col, $def);
}

if (table_exists($pdo, 'orders')) {
    foreach ([
        'plan_id' => 'INT NULL AFTER `amount`',
        'payment_id' => 'INT NULL AFTER `plan_id`',
        'payment_provider' => 'VARCHAR(64) NULL AFTER `payment_id`',
        'updated_at' => 'DATETIME NULL',
    ] as $col => $def) {
        add_column($pdo, 'orders', $col, $def);
    }
}

$providers = [
    ['manual', 'Ручная оплата', 1, '{}'],
    ['cryptocloud', 'CryptoCloud', 0, json_encode(['api_key'=>'','shop_id'=>'','currency'=>'RUB'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)],
    ['aaio', 'AAIO', 0, json_encode(['merchant_id'=>'','secret_key'=>'','currency'=>'RUB'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)],
];
$stmt = $pdo->prepare("INSERT INTO payment_providers (code,title,is_active,config_json) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title), config_json=IF(config_json IS NULL OR config_json='', VALUES(config_json), config_json)");
foreach ($providers as $p) {
    $stmt->execute($p);
}

$planCount = (int)$pdo->query("SELECT COUNT(*) FROM plans")->fetchColumn();
if ($planCount === 0) {
    $stmt = $pdo->prepare("INSERT INTO plans (title,days,price,old_price,badge,description,is_active,is_popular,sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ([
        ['7 дней', 7, 199, null, 'Старт', 'Короткий доступ для проверки скорости.', 1, 0, 10],
        ['30 дней', 30, 499, 699, 'Хит', 'Оптимальный тариф на месяц.', 1, 1, 20],
        ['90 дней', 90, 1199, 1499, 'Выгодно', 'Три месяца доступа ко всем локациям.', 1, 0, 30],
        ['180 дней', 180, 1999, 2999, 'Максимум', 'Полгода доступа ко всем локациям.', 1, 0, 40],
    ] as $p) {
        $stmt->execute($p);
    }
}

echo "OK: payments/plans schema is ready\n";
