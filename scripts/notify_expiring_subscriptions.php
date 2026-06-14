<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
init_db();

$daysList = [3, 1];
$sent = 0; $skipped = 0;
foreach ($daysList as $days) {
    $flag = $days === 3 ? 'reminded_3' : 'reminded_1';
    $stmt = db()->prepare("SELECT s.*, u.email, u.id AS uid FROM subscriptions s JOIN users u ON u.id=s.user_id WHERE s.server_id='global' AND s.active=1 AND s.$flag=0 AND s.expires_at > UTC_TIMESTAMP() AND s.expires_at <= (UTC_TIMESTAMP() + INTERVAL ? DAY)");
    $stmt->execute([$days]);
    foreach ($stmt->fetchAll() as $row) {
        $subject = "MagicVPN: подписка заканчивается через {$days} дн.";
        $body = "Здравствуйте!\n\nВаша подписка MagicVPN заканчивается: {$row['expires_at']} UTC.\n\nПродлить можно в личном кабинете: " . base_url() . "/dashboard\n";
        if (send_magic_email((int)$row['uid'], $row['email'], $subject, $body)) $sent++; else $skipped++;
        db()->prepare("UPDATE subscriptions SET $flag=1 WHERE id=?")->execute([(int)$row['id']]);
    }
}
echo "Done. sent={$sent}, skipped={$skipped}\n";
