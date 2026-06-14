<?php
declare(strict_types=1);

function password_reset_token_hash(string $token): string {
    return hash('sha256', $token);
}

function password_reset_send(string $email): bool {
    $stmt = db()->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) {
        // Не раскрываем, существует ли аккаунт.
        return true;
    }

    // Простая защита от частых запросов: не чаще 1 раза в 2 минуты.
    if (!empty($user['password_reset_sent_at']) && strtotime((string)$user['password_reset_sent_at']) > time() - 120) {
        return true;
    }

    $token = random_token(32);
    $hash = password_reset_token_hash($token);
    db()->prepare("UPDATE users SET password_reset_token_hash=?, password_reset_expires_at=DATE_ADD(UTC_TIMESTAMP(), INTERVAL 60 MINUTE), password_reset_sent_at=UTC_TIMESTAMP() WHERE id=?")
        ->execute([$hash, (int)$user['id']]);

    $link = base_url() . '/reset-password?token=' . urlencode($token);
    $subject = 'MagicVPN: восстановление пароля';
    $body = "Здравствуйте!\n\nВы запросили восстановление пароля MagicVPN.\n\nНажмите на ссылку, чтобы задать новый пароль:\n{$link}\n\nСсылка действует 60 минут.\n\nЕсли вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.";
    return send_magic_email((int)$user['id'], (string)$user['email'], $subject, $body);
}

function password_reset_user_by_token(string $token): ?array {
    if ($token === '') return null;
    $hash = password_reset_token_hash($token);
    $stmt = db()->prepare("SELECT * FROM users WHERE password_reset_token_hash=? AND password_reset_expires_at > UTC_TIMESTAMP() LIMIT 1");
    $stmt->execute([$hash]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function password_reset_complete(string $token, string $password): bool {
    $user = password_reset_user_by_token($token);
    if (!$user) return false;
    db()->prepare('UPDATE users SET password_hash=?, password_reset_token_hash=NULL, password_reset_expires_at=NULL, password_reset_sent_at=NULL, email_verified=1 WHERE id=?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), (int)$user['id']]);
    admin_log('password_reset_completed', (int)$user['id'], ['email' => $user['email'] ?? null]);
    return true;
}
