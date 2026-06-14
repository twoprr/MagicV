<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$pdo = db();

if (!function_exists('pw_column_exists')) {
    function pw_column_exists(PDO $pdo, string $table, string $column): bool {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

if (!pw_column_exists($pdo, 'users', 'password_reset_token_hash')) {
    $pdo->exec('ALTER TABLE users ADD COLUMN password_reset_token_hash VARCHAR(128) NULL');
}
if (!pw_column_exists($pdo, 'users', 'password_reset_expires_at')) {
    $pdo->exec('ALTER TABLE users ADD COLUMN password_reset_expires_at DATETIME NULL');
}
if (!pw_column_exists($pdo, 'users', 'password_reset_sent_at')) {
    $pdo->exec('ALTER TABLE users ADD COLUMN password_reset_sent_at DATETIME NULL');
}

try {
    $pdo->exec('CREATE INDEX idx_users_password_reset_token_hash ON users (password_reset_token_hash)');
} catch (Throwable $e) {
    // Индекс уже может существовать.
}

echo "OK: password reset schema is ready\n";
