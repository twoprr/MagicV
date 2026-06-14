<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

init_db();
$pdo = db();

function setting_put(string $key, string $value): void {
    db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')
        ->execute([$key, $value]);
}

$pdo->exec("CREATE TABLE IF NOT EXISTS user_balances (
    user_id INT NOT NULL PRIMARY KEY,
    balance INT NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_balances_balance (balance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS balance_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount INT NOT NULL,
    type VARCHAR(40) NOT NULL,
    comment TEXT NULL,
    related_type VARCHAR(60) NULL,
    related_id INT NULL,
    admin_user_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_balance_tx_user (user_id, created_at),
    INDEX idx_balance_tx_type (type),
    INDEX idx_balance_tx_related (related_type, related_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS balance_topups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount INT NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'pending',
    receipt_path VARCHAR(255) NULL,
    admin_comment TEXT NULL,
    processed_by INT NULL,
    processed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_balance_topups_user (user_id, created_at),
    INDEX idx_balance_topups_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

foreach ([
    'registration_captcha_provider' => 'google',
    'recaptcha_site_key' => '',
    'recaptcha_secret_key' => '',
    'registration_captcha_label' => 'Подтвердите, что вы не робот',
] as $k => $v) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
    $stmt->execute([$k, $v]);
}

// Создаём строки баланса для существующих пользователей.
$pdo->exec("INSERT IGNORE INTO user_balances (user_id,balance) SELECT id,0 FROM users WHERE role='user'");

echo "OK: balance + Google reCAPTCHA schema/settings are ready\n";
