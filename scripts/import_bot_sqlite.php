<?php

declare(strict_types=1);

// Usage:
// php scripts/import_bot_sqlite.php /opt/vpn_shop_bot/database/db.sqlite3

require __DIR__ . '/../app/bootstrap.php';
init_db();

$botDbPath = $argv[1] ?? '';
if (!$botDbPath || !is_file($botDbPath)) {
    fwrite(STDERR, "Usage: php scripts/import_bot_sqlite.php /path/to/bot.sqlite\n");
    exit(1);
}

$bot = new PDO('sqlite:' . $botDbPath);
$bot->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$bot->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$now = gmdate('Y-m-d H:i:s');

$subs = $bot->query("SELECT * FROM subscriptions WHERE active = 1 AND datetime(expires_at) > datetime('now') ORDER BY user_id ASC, datetime(expires_at) DESC")->fetchAll();
$usersById = [];
try {
    foreach ($bot->query("SELECT * FROM users")->fetchAll() as $u) {
        $usersById[(int)$u['id']] = $u;
    }
} catch (Throwable $e) {
    // Some bot databases may not have a users table with the expected structure.
}

$best = [];
foreach ($subs as $s) {
    $uid = (int)$s['user_id'];
    if (!isset($best[$uid]) || strtotime((string)$s['expires_at']) > strtotime((string)$best[$uid]['expires_at'])) {
        $best[$uid] = $s;
    }
}

$createdUsers = 0;
$updatedUsers = 0;
$createdSubs = 0;
$updatedSubs = 0;

foreach ($best as $tgId => $sub) {
    $botUser = $usersById[$tgId] ?? [];
    $username = trim((string)($botUser['username'] ?? ''));
    $name = $username ? '@' . $username : 'Telegram ' . $tgId;
    $email = 'tg_' . $tgId . '@magicvpn.local';

    $stmt = db()->prepare('SELECT * FROM users WHERE telegram_id = ? OR email = ? LIMIT 1');
    $stmt->execute([$tgId, $email]);
    $webUser = $stmt->fetch();

    if ($webUser) {
        db()->prepare('UPDATE users SET telegram_id=?, bot_user_id=?, name=? WHERE id=?')->execute([$tgId, $tgId, $name, $webUser['id']]);
        $userId = (int)$webUser['id'];
        $updatedUsers++;
    } else {
        db()->prepare('INSERT INTO users (email,password_hash,name,telegram_id,bot_user_id,role) VALUES (?,?,?,?,?,?)')->execute([
            $email,
            password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            $name,
            $tgId,
            $tgId,
            'user'
        ]);
        $userId = (int)db()->lastInsertId();
        $createdUsers++;
    }

    $uuid = (string)$sub['uuid'];
    $expires = gmdate('Y-m-d H:i:s', strtotime((string)$sub['expires_at']));

    $stmt = db()->prepare("SELECT * FROM subscriptions WHERE user_id=? AND server_id='global' ORDER BY expires_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $global = $stmt->fetch();

    if ($global) {
        $newExpires = strtotime($expires) > strtotime((string)$global['expires_at']) ? $expires : $global['expires_at'];
        db()->prepare('UPDATE subscriptions SET uuid=?, active=1, expires_at=?, reminded_3=0, reminded_1=0 WHERE id=?')->execute([$uuid, $newExpires, $global['id']]);
        $updatedSubs++;
    } else {
        db()->prepare("INSERT INTO subscriptions (user_id,server_id,uuid,active,expires_at) VALUES (?,'global',?,1,?)")->execute([$userId, $uuid, $expires]);
        $createdSubs++;
    }
}

echo "Import complete\n";
echo "Users: created={$createdUsers}, updated={$updatedUsers}\n";
echo "Subscriptions: created={$createdSubs}, updated={$updatedSubs}\n";
echo "Processed Telegram users: " . count($best) . "\n";
echo "Note: users are created with email tg_<telegram_id>@magicvpn.local. They need password reset/manual password assignment to log in by email.\n";
