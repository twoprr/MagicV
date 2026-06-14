<?php
declare(strict_types=1);

session_start();

$app = require __DIR__ . '/../config/app.php';
$servers = require __DIR__ . '/../config/servers.php';

function app_config(?string $key = null) {
    global $app;
    return $key === null ? $app : ($app[$key] ?? null);
}

function servers_config(): array {
    global $servers;
    return $servers;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $db = require __DIR__ . '/../config/database.php';
    $driver = $db['driver'] ?? 'mysql';
    if ($driver !== 'mysql') {
        throw new RuntimeException('Only MySQL driver is configured in this build.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'] ?? '127.0.0.1',
        (int)($db['port'] ?? 3306),
        $db['database'] ?? 'magicvpn',
        $db['charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $db['username'] ?? '', $db['password'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function init_db(): void {
    $pdo = db();
    $schema = file_get_contents(__DIR__ . '/../database/schema_mysql.sql');
    $pdo->exec($schema);
    ensure_schema_columns($pdo);

    $adminEmail = app_config('admin_email');
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$adminEmail]);
    if (!$stmt->fetch()) {
        $pdo->prepare('INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, ?)')->execute([
            $adminEmail,
            password_hash(app_config('admin_password'), PASSWORD_DEFAULT),
            'Administrator',
            'admin'
        ]);
    }
}

function table_name(string $name): string {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
}

function column_exists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function index_exists(PDO $pdo, string $table, string $index): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
    $stmt->execute([$table, $index]);
    return (int)$stmt->fetchColumn() > 0;
}

function add_index_if_missing(PDO $pdo, string $table, string $index, string $definition): void {
    if (!index_exists($pdo, $table, $index)) {
        $pdo->exec("CREATE INDEX `" . table_name($index) . "` ON `" . table_name($table) . "` ($definition)");
    }
}

function ensure_schema_columns(PDO $pdo): void {
    if (!column_exists($pdo, 'users', 'telegram_id')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN telegram_id BIGINT NULL');
    }
    if (!column_exists($pdo, 'users', 'bot_user_id')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN bot_user_id BIGINT NULL');
    }
    if (!column_exists($pdo, 'subscriptions', 'sub_token')) {
        $pdo->exec('ALTER TABLE subscriptions ADD COLUMN sub_token VARCHAR(96) NULL');
    }
    if (!column_exists($pdo, 'orders', 'receipt_path')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN receipt_path VARCHAR(255) NULL');
    }
    if (!column_exists($pdo, 'orders', 'last_error')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN last_error TEXT NULL');
    }
    if (!column_exists($pdo, 'orders', 'promo_code')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN promo_code VARCHAR(64) NULL');
    }
    if (!column_exists($pdo, 'orders', 'original_amount')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN original_amount INT NULL');
    }
    if (!column_exists($pdo, 'orders', 'discount_amount')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN discount_amount INT NOT NULL DEFAULT 0');
    }
    if (!column_exists($pdo, 'users', 'trial_used')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN trial_used TINYINT(1) NOT NULL DEFAULT 0');
    }
    if (!column_exists($pdo, 'users', 'email_verified')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 1');
    }
    if (!column_exists($pdo, 'users', 'email_verify_token')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN email_verify_token VARCHAR(128) NULL');
    }
    if (!column_exists($pdo, 'users', 'email_verify_expires_at')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN email_verify_expires_at DATETIME NULL');
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_user_id INT NULL,
        action VARCHAR(80) NOT NULL,
        target_user_id INT NULL,
        details TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_admin_logs_created_at (created_at),
        INDEX idx_admin_logs_action (action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS promo_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(64) NOT NULL UNIQUE,
        type VARCHAR(20) NOT NULL,
        value INT NOT NULL,
        max_uses INT NULL,
        used_count INT NOT NULL DEFAULT 0,
        active TINYINT(1) NOT NULL DEFAULT 1,
        expires_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_promo_codes_code (code),
        INDEX idx_promo_codes_active (active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS promo_uses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        promo_id INT NOT NULL,
        user_id INT NOT NULL,
        order_id INT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_promo_user (promo_id, user_id),
        INDEX idx_promo_uses_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(190) NOT NULL,
        ip VARCHAR(64) NOT NULL,
        success TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_login_attempts_email_ip (email, ip, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS email_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        email VARCHAR(190) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        status VARCHAR(32) NOT NULL,
        error_text TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email_logs_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");


    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(120) PRIMARY KEY,
        setting_value TEXT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_notices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        target_type VARCHAR(20) NOT NULL DEFAULT 'all',
        user_id INT NULL,
        title VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        level VARCHAR(20) NOT NULL DEFAULT 'info',
        pinned TINYINT(1) NOT NULL DEFAULT 1,
        active TINYINT(1) NOT NULL DEFAULT 1,
        starts_at DATETIME NULL,
        expires_at DATETIME NULL,
        created_by INT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_notices_active (active, starts_at, expires_at),
        INDEX idx_notices_target (target_type, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $defaults = [
        'smtp_enabled' => '0',
        'smtp_host' => '',
        'smtp_port' => '587',
        'smtp_encryption' => 'tls',
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_from_email' => 'no-reply@magicvpn.local',
        'smtp_from_name' => 'MagicVPN',
        'smtp_timeout' => '20',
        'registration_email_verify_enabled' => '0',
        'registration_captcha_enabled' => '0',
        'registration_captcha_label' => 'Подтвердите, что вы не робот',
        'registration_captcha_provider' => 'google',
        'recaptcha_site_key' => '',
        'recaptcha_secret_key' => '',
    ];
    foreach ($defaults as $k => $v) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
        $stmt->execute([$k, $v]);
    }

    if (!column_exists($pdo, 'users', 'ref_code')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN ref_code VARCHAR(32) NULL');
    }
    if (!column_exists($pdo, 'users', 'referred_by_user_id')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN referred_by_user_id INT NULL');
    }
    if (!column_exists($pdo, 'users', 'referral_rewarded')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN referral_rewarded TINYINT(1) NOT NULL DEFAULT 0');
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(80) NOT NULL,
        payload JSON NULL,
        status VARCHAR(24) NOT NULL DEFAULT 'pending',
        attempts INT NOT NULL DEFAULT 0,
        max_attempts INT NOT NULL DEFAULT 5,
        last_error TEXT NULL,
        available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        started_at DATETIME NULL,
        finished_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_jobs_status_available (status, available_at),
        INDEX idx_jobs_type (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS support_tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject VARCHAR(255) NOT NULL,
        status VARCHAR(24) NOT NULL DEFAULT 'open',
        priority VARCHAR(20) NOT NULL DEFAULT 'normal',
        last_message_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tickets_user (user_id),
        INDEX idx_tickets_status (status),
        INDEX idx_tickets_last_message (last_message_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS support_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        user_id INT NULL,
        is_admin TINYINT(1) NOT NULL DEFAULT 0,
        body TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ticket_messages_ticket (ticket_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");


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

    $defaults_more = [
        'referral_enabled' => '1',
        'referral_reward_days' => '7',
        'support_enabled' => '1',
        'jobs_inline_after_approve' => '0',
    ];
    foreach ($defaults_more as $k => $v) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
        $stmt->execute([$k, $v]);
    }

    add_index_if_missing($pdo, 'users', 'idx_users_telegram_id', 'telegram_id');
    add_index_if_missing($pdo, 'users', 'idx_users_email_verify_token', 'email_verify_token');
    add_index_if_missing($pdo, 'users', 'idx_users_ref_code', 'ref_code');
    add_index_if_missing($pdo, 'users', 'idx_users_referred_by', 'referred_by_user_id');
    add_index_if_missing($pdo, 'subscriptions', 'idx_subscriptions_token', 'sub_token');
    add_index_if_missing($pdo, 'subscriptions', 'idx_subscriptions_global', 'user_id, server_id, expires_at');
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path); exit; }
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
function require_login(): array { $u = current_user(); if (!$u) redirect('/login'); return $u; }
function require_admin(): array { $u = require_login(); if (($u['role'] ?? '') !== 'admin') redirect('/dashboard'); return $u; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function check_csrf(): void { if (($_POST['_csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) { http_response_code(419); exit('CSRF error'); } }
function flash(?string $msg = null, string $type = 'success'): ?array {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function view(string $template, array $data = []): void {
    extract($data);
    $user = current_user();
    $appName = app_config('app_name');
    include __DIR__ . '/Views/layouts/main.php';
}
function render_template(string $template, array $data = []): void {
    extract($data);
    include __DIR__ . '/Views/' . $template . '.php';
}
function uuid_v4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
function random_token(int $bytes = 32): string { return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '='); }

function base_url(): string {
    $cfg = rtrim((string)(app_config('base_url') ?? ''), '/');
    if ($cfg !== '') return $cfg;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function ensure_subscription_token(array $sub): string {
    if (!empty($sub['sub_token'])) return (string)$sub['sub_token'];
    $token = random_token(32);
    db()->prepare('UPDATE subscriptions SET sub_token=? WHERE id=?')->execute([$token, $sub['id']]);
    return $token;
}

function admin_log(string $action, ?int $targetUserId = null, array|string|null $details = null): void {
    $adminId = $_SESSION['user_id'] ?? null;
    $detailsText = is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $details;
    try {
        db()->prepare('INSERT INTO admin_logs (admin_user_id, action, target_user_id, details) VALUES (?, ?, ?, ?)')
            ->execute([$adminId, $action, $targetUserId, $detailsText]);
    } catch (Throwable $e) {
        // Логи не должны ломать бизнес-действие.
    }
}


function login_blocked(string $email, string $ip): bool {
    $sec = app_config('security') ?: [];
    $max = (int)($sec['max_login_attempts'] ?? 5);
    $minutes = (int)($sec['login_window_minutes'] ?? 15);
    $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE email=? AND ip=? AND success=0 AND created_at >= (UTC_TIMESTAMP() - INTERVAL ? MINUTE)');
    $stmt->execute([$email, $ip, $minutes]);
    return (int)$stmt->fetchColumn() >= $max;
}

function record_login_attempt(string $email, string $ip, bool $success): void {
    try {
        db()->prepare('INSERT INTO login_attempts (email, ip, success) VALUES (?, ?, ?)')->execute([$email, $ip, $success ? 1 : 0]);
    } catch (Throwable $e) {}
}

function find_valid_promo(string $code, int $userId): ?array {
    $code = strtoupper(trim($code));
    if ($code === '') return null;
    $stmt = db()->prepare("SELECT * FROM promo_codes WHERE UPPER(code)=? AND active=1 AND (expires_at IS NULL OR expires_at > UTC_TIMESTAMP()) LIMIT 1");
    $stmt->execute([$code]);
    $promo = $stmt->fetch();
    if (!$promo) return null;
    if ($promo['max_uses'] !== null && (int)$promo['used_count'] >= (int)$promo['max_uses']) return null;
    $used = db()->prepare('SELECT COUNT(*) FROM promo_uses WHERE promo_id=? AND user_id=?');
    $used->execute([(int)$promo['id'], $userId]);
    if ((int)$used->fetchColumn() > 0) return null;
    return $promo;
}

function apply_promo_to_price(int $amount, int $days, ?array $promo): array {
    if (!$promo) return ['amount'=>$amount, 'discount'=>0, 'extra_days'=>0, 'promo_code'=>null];
    $type = (string)$promo['type'];
    $value = max(0, (int)$promo['value']);
    $discount = 0; $extraDays = 0;
    if ($type === 'percent') $discount = (int)floor($amount * min($value, 100) / 100);
    elseif ($type === 'fixed') $discount = min($amount, $value);
    elseif ($type === 'days') $extraDays = $value;
    $final = max(0, $amount - $discount);
    return ['amount'=>$final, 'discount'=>$discount, 'extra_days'=>$extraDays, 'promo_code'=>$promo['code']];
}

function mark_promo_used(array $promo, int $userId, int $orderId): void {
    db()->prepare('INSERT IGNORE INTO promo_uses (promo_id, user_id, order_id) VALUES (?, ?, ?)')->execute([(int)$promo['id'], $userId, $orderId]);
    db()->prepare('UPDATE promo_codes SET used_count = used_count + 1 WHERE id=?')->execute([(int)$promo['id']]);
}

function setting_get(string $key, ?string $default = null): ?string {
    try {
        $stmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key=? LIMIT 1');
        $stmt->execute([$key]);
        $v = $stmt->fetchColumn();
        return $v === false ? $default : (string)$v;
    } catch (Throwable $e) {
        return $default;
    }
}

function setting_set(string $key, ?string $value): void {
    db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')
        ->execute([$key, $value]);
}

function setting_enabled(string $key): bool {
    return setting_get($key, '0') === '1';
}

function captcha_enabled(): bool {
    return setting_enabled('registration_captcha_enabled');
}

function email_verification_enabled(): bool {
    return setting_enabled('registration_email_verify_enabled');
}

function captcha_provider(): string {
    return setting_get('registration_captcha_provider', 'google') ?: 'google';
}

function captcha_current(): array {
    if (!captcha_enabled()) return [];
    return [
        'provider' => captcha_provider(),
        'site_key' => setting_get('recaptcha_site_key', '') ?: '',
        'label' => setting_get('registration_captcha_label', 'Подтвердите, что вы не робот') ?: 'Подтвердите, что вы не робот',
    ];
}

function captcha_generate(): array {
    return captcha_current();
}

function captcha_check(): bool {
    if (!captcha_enabled()) return true;
    $provider = captcha_provider();
    if ($provider !== 'google') return true;

    $secret = trim((string)setting_get('recaptcha_secret_key', ''));
    $token = trim((string)($_POST['g-recaptcha-response'] ?? ''));
    if ($secret === '' || $token === '') return false;

    $payload = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $response = null;
    if (function_exists('curl_init')) {
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 10,
        ]]);
        $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    }

    $json = is_string($response) ? json_decode($response, true) : null;
    return is_array($json) && !empty($json['success']);
}

function send_email_verification(array $user): bool {
    $token = random_token(32);
    $expires = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('+24 hours')->format('Y-m-d H:i:s');
    db()->prepare('UPDATE users SET email_verify_token=?, email_verify_expires_at=? WHERE id=?')->execute([$token, $expires, (int)$user['id']]);
    $url = base_url() . '/verify-email?token=' . urlencode($token);
    $body = "Здравствуйте!\n\nПодтвердите регистрацию в MagicVPN по ссылке:\n{$url}\n\nСсылка действует 24 часа.\n\nЕсли вы не регистрировались в MagicVPN, просто игнорируйте это письмо.";
    return send_magic_email((int)$user['id'], (string)$user['email'], 'MagicVPN: подтверждение Email', $body);
}

function smtp_config(): array {
    $mail = app_config('mail') ?: [];
    return [
        'enabled' => setting_get('smtp_enabled', !empty($mail['enabled']) ? '1' : '0') === '1',
        'host' => setting_get('smtp_host', ''),
        'port' => (int)(setting_get('smtp_port', '587') ?: 587),
        'encryption' => setting_get('smtp_encryption', 'tls') ?: 'tls',
        'username' => setting_get('smtp_username', ''),
        'password' => setting_get('smtp_password', ''),
        'from_email' => setting_get('smtp_from_email', (string)($mail['from'] ?? 'no-reply@magicvpn.local')),
        'from_name' => setting_get('smtp_from_name', (string)($mail['from_name'] ?? 'MagicVPN')),
        'timeout' => (int)(setting_get('smtp_timeout', '20') ?: 20),
    ];
}

function smtp_read($fp): string {
    $data = '';
    while (($line = fgets($fp, 515)) !== false) {
        $data .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') break;
    }
    return $data;
}

function smtp_expect($fp, array $codes): string {
    $resp = smtp_read($fp);
    $code = (int)substr($resp, 0, 3);
    if (!in_array($code, $codes, true)) {
        throw new RuntimeException('SMTP error: ' . trim($resp));
    }
    return $resp;
}

function smtp_cmd($fp, string $cmd, array $codes): string {
    fwrite($fp, $cmd . "\r\n");
    return smtp_expect($fp, $codes);
}

function smtp_send_raw(array $cfg, string $to, string $subject, string $body): void {
    if (empty($cfg['host'])) throw new RuntimeException('SMTP host is empty');
    $transport = ($cfg['encryption'] === 'ssl') ? 'ssl://' : '';
    $fp = @stream_socket_client($transport . $cfg['host'] . ':' . (int)$cfg['port'], $errno, $errstr, (int)$cfg['timeout']);
    if (!$fp) throw new RuntimeException("SMTP connect failed: {$errstr} ({$errno})");
    stream_set_timeout($fp, (int)$cfg['timeout']);

    smtp_expect($fp, [220]);
    smtp_cmd($fp, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'magicvpn.local'), [250]);
    if ($cfg['encryption'] === 'tls') {
        smtp_cmd($fp, 'STARTTLS', [220]);
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('SMTP STARTTLS failed');
        }
        smtp_cmd($fp, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'magicvpn.local'), [250]);
    }
    if (!empty($cfg['username'])) {
        smtp_cmd($fp, 'AUTH LOGIN', [334]);
        smtp_cmd($fp, base64_encode((string)$cfg['username']), [334]);
        smtp_cmd($fp, base64_encode((string)$cfg['password']), [235]);
    }

    $fromEmail = (string)$cfg['from_email'];
    $fromName = mb_encode_mimeheader((string)$cfg['from_name']);
    smtp_cmd($fp, 'MAIL FROM:<' . $fromEmail . '>', [250]);
    smtp_cmd($fp, 'RCPT TO:<' . $to . '>', [250, 251]);
    smtp_cmd($fp, 'DATA', [354]);
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $message = "From: {$fromName} <{$fromEmail}>\r\n";
    $message .= "To: <{$to}>\r\n";
    $message .= "Subject: {$encodedSubject}\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= str_replace(["\r\n.", "\n."], ["\r\n..", "\n.."], $body) . "\r\n.";
    fwrite($fp, $message . "\r\n");
    smtp_expect($fp, [250]);
    @smtp_cmd($fp, 'QUIT', [221]);
    fclose($fp);
}

function send_magic_email(?int $userId, string $email, string $subject, string $body): bool {
    $cfg = smtp_config();
    if (empty($cfg['enabled'])) {
        try { db()->prepare('INSERT INTO email_logs (user_id,email,subject,status,error_text) VALUES (?,?,?,?,?)')->execute([$userId,$email,$subject,'skipped','SMTP disabled']); } catch (Throwable $e) {}
        return false;
    }
    try {
        smtp_send_raw($cfg, $email, $subject, $body);
        db()->prepare('INSERT INTO email_logs (user_id,email,subject,status,error_text) VALUES (?,?,?,?,?)')->execute([$userId,$email,$subject,'sent',null]);
        return true;
    } catch (Throwable $e) {
        try { db()->prepare('INSERT INTO email_logs (user_id,email,subject,status,error_text) VALUES (?,?,?,?,?)')->execute([$userId,$email,$subject,'failed',$e->getMessage()]); } catch (Throwable $ignored) {}
        return false;
    }
}

function active_notices(?int $userId): array {
    if (!$userId) return [];
    $stmt = db()->prepare("SELECT * FROM user_notices
        WHERE active=1
          AND (starts_at IS NULL OR starts_at <= UTC_TIMESTAMP())
          AND (expires_at IS NULL OR expires_at > UTC_TIMESTAMP())
          AND (target_type='all' OR (target_type='user' AND user_id=?))
        ORDER BY pinned DESC, created_at DESC
        LIMIT 10");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}


function create_or_extend_trial(array $user): array {
    $trial = app_config('trial') ?: [];
    if (empty($trial['enabled'])) throw new RuntimeException('Тестовый доступ временно отключён.');
    if (!empty($user['trial_used'])) throw new RuntimeException('Тестовый доступ уже был использован.');
    $stmt = db()->prepare("SELECT * FROM subscriptions WHERE user_id=? AND server_id='global' AND active=1 AND expires_at > UTC_TIMESTAMP() LIMIT 1");
    $stmt->execute([(int)$user['id']]);
    if ($stmt->fetch()) throw new RuntimeException('У вас уже есть активная подписка.');
    $uuid = uuid_v4();
    $days = max(1, (int)($trial['days'] ?? 1));
    $expires = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('+' . $days . ' days')->format('Y-m-d H:i:s');
    db()->prepare("INSERT INTO subscriptions (user_id,server_id,uuid,active,expires_at,sub_token) VALUES (?,'global',?,1,?,?)")->execute([(int)$user['id'],$uuid,$expires,random_token(32)]);
    db()->prepare('UPDATE users SET trial_used=1 WHERE id=?')->execute([(int)$user['id']]);
    return ['uuid'=>$uuid, 'expires_at'=>$expires, 'days'=>$days];
}


function user_ref_code(array $user): string {
    if (!empty($user['ref_code'])) return (string)$user['ref_code'];
    do {
        $code = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE ref_code=?');
        $stmt->execute([$code]);
    } while ((int)$stmt->fetchColumn() > 0);
    db()->prepare('UPDATE users SET ref_code=? WHERE id=?')->execute([$code, (int)$user['id']]);
    return $code;
}

function referral_link(array $user): string {
    return base_url() . '/r/' . urlencode(user_ref_code($user));
}

function apply_referral_on_registration(int $newUserId): void {
    $ref = (string)($_SESSION['ref_code'] ?? '');
    if ($ref === '') return;
    $stmt = db()->prepare('SELECT id FROM users WHERE ref_code=? AND id<>? LIMIT 1');
    $stmt->execute([$ref, $newUserId]);
    $referrerId = $stmt->fetchColumn();
    if ($referrerId) {
        db()->prepare('UPDATE users SET referred_by_user_id=? WHERE id=?')->execute([(int)$referrerId, $newUserId]);
    }
}

function maybe_reward_referrer(int $paidUserId): void {
    if (setting_get('referral_enabled', '1') !== '1') return;
    $stmt = db()->prepare('SELECT id, referred_by_user_id, referral_rewarded FROM users WHERE id=? LIMIT 1');
    $stmt->execute([$paidUserId]);
    $u = $stmt->fetch();
    if (!$u || empty($u['referred_by_user_id']) || !empty($u['referral_rewarded'])) return;
    $rewardDays = max(1, (int)(setting_get('referral_reward_days', '7') ?: 7));
    extend_user((int)$u['referred_by_user_id'], $rewardDays, false);
    db()->prepare('UPDATE users SET referral_rewarded=1 WHERE id=?')->execute([$paidUserId]);
    admin_log('referral_rewarded', (int)$u['referred_by_user_id'], ['paid_user_id'=>$paidUserId, 'days'=>$rewardDays]);
}

function create_job(string $type, array $payload, int $maxAttempts = 5, ?string $availableAt = null): int {
    $availableAt = $availableAt ?: gmdate('Y-m-d H:i:s');
    db()->prepare('INSERT INTO jobs (type,payload,status,max_attempts,available_at) VALUES (?,?,\'pending\',?,?)')
        ->execute([$type, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $maxAttempts, $availableAt]);
    return (int)db()->lastInsertId();
}

function job_stats(): array {
    $rows = db()->query("SELECT status, COUNT(*) c FROM jobs GROUP BY status")->fetchAll();
    $out = ['pending'=>0,'running'=>0,'done'=>0,'failed'=>0];
    foreach ($rows as $r) $out[(string)$r['status']] = (int)$r['c'];
    return $out;
}

function process_one_job(array $job): void {
    $payload = json_decode((string)($job['payload'] ?? '{}'), true) ?: [];
    if ($job['type'] === 'provision_user') {
        require_once __DIR__ . '/Services/XrayProvision.php';
        $uuid = (string)($payload['uuid'] ?? '');
        $email = (string)($payload['xray_email'] ?? ('web_' . ($payload['user_id'] ?? '')));
        if ($uuid === '') throw new RuntimeException('Job provision_user: empty uuid');
        $results = ensure_vless_user_everywhere($uuid, $email);
        $failed = array_values(array_filter($results, fn($r) => empty($r['ok'])));
        if ($failed) throw new RuntimeException(json_encode($failed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return;
    }
    if ($job['type'] === 'sync_active_user') {
        require_once __DIR__ . '/Services/XrayProvision.php';
        $userId = (int)($payload['user_id'] ?? 0);
        $stmt = db()->prepare("SELECT * FROM subscriptions WHERE user_id=? AND server_id='global' AND active=1 AND expires_at > UTC_TIMESTAMP() ORDER BY expires_at DESC LIMIT 1");
        $stmt->execute([$userId]);
        $sub = $stmt->fetch();
        if (!$sub) return;
        $results = ensure_vless_user_everywhere((string)$sub['uuid'], 'web_' . $userId);
        $failed = array_values(array_filter($results, fn($r) => empty($r['ok'])));
        if ($failed) throw new RuntimeException(json_encode($failed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return;
    }
    throw new RuntimeException('Unknown job type: ' . $job['type']);
}

function run_jobs(int $limit = 5): array {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE status='pending' AND available_at <= UTC_TIMESTAMP() ORDER BY id ASC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $jobs = $stmt->fetchAll();
    $done = 0; $failed = 0;
    foreach ($jobs as $job) {
        $pdo->prepare("UPDATE jobs SET status='running', attempts=attempts+1, started_at=UTC_TIMESTAMP(), last_error=NULL WHERE id=?")->execute([(int)$job['id']]);
        try {
            process_one_job($job);
            $pdo->prepare("UPDATE jobs SET status='done', finished_at=UTC_TIMESTAMP() WHERE id=?")->execute([(int)$job['id']]);
            $done++;
        } catch (Throwable $e) {
            $attempts = (int)$job['attempts'] + 1;
            $status = $attempts >= (int)$job['max_attempts'] ? 'failed' : 'pending';
            $next = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('+' . min(60, 2 ** min($attempts, 6)) . ' minutes')->format('Y-m-d H:i:s');
            $pdo->prepare("UPDATE jobs SET status=?, last_error=?, available_at=?, finished_at=IF(?='failed', UTC_TIMESTAMP(), NULL) WHERE id=?")
                ->execute([$status, $e->getMessage(), $next, $status, (int)$job['id']]);
            $failed++;
        }
    }
    return ['processed'=>count($jobs),'done'=>$done,'failed'=>$failed];
}

function enqueue_sync_all_active_users(): int {
    $rows = db()->query("SELECT user_id FROM subscriptions WHERE server_id='global' AND active=1 AND expires_at > UTC_TIMESTAMP()")->fetchAll();
    foreach ($rows as $r) create_job('sync_active_user', ['user_id'=>(int)$r['user_id']], 5);
    return count($rows);
}

function admin_dashboard_stats(): array {
    $pdo = db();
    $stats = job_stats();
    return [
        'pending_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
        'users' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
        'active_subs' => (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE server_id='global' AND active=1 AND expires_at > UTC_TIMESTAMP()")->fetchColumn(),
        'expiring_3d' => (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE server_id='global' AND active=1 AND expires_at > UTC_TIMESTAMP() AND expires_at <= (UTC_TIMESTAMP() + INTERVAL 3 DAY)")->fetchColumn(),
        'revenue_month' => (int)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status='paid' AND created_at >= DATE_FORMAT(UTC_TIMESTAMP(), '%Y-%m-01 00:00:00')")->fetchColumn(),
        'open_tickets' => (int)$pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('open','answered')")->fetchColumn(),
        'jobs_pending' => $stats['pending'] ?? 0,
        'jobs_failed' => $stats['failed'] ?? 0,
    ];
}

function ticket_status_label(string $status): string {
    return match($status) {
        'open' => 'Открыт',
        'answered' => 'Ответ админа',
        'closed' => 'Закрыт',
        default => $status,
    };
}
