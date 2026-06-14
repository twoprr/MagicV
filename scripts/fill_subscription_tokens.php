<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
init_db();

$stmt = db()->query("SELECT * FROM subscriptions WHERE server_id='global' AND (sub_token IS NULL OR sub_token='')");
$rows = $stmt->fetchAll();
foreach ($rows as $sub) {
    ensure_subscription_token($sub);
    echo "token generated for subscription {$sub['id']} user {$sub['user_id']}\n";
}
echo "Done: " . count($rows) . "\n";
