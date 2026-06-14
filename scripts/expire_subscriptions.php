<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Services/VpnLinks.php';
require __DIR__ . '/../app/Services/XrayProvision.php';

init_db();

$dryRun = in_array('--dry-run', $argv, true);
$noXray = in_array('--no-xray', $argv, true);
$limit = 100;

$stmt = db()->prepare("SELECT * FROM subscriptions WHERE server_id='global' AND active=1 AND expires_at <= UTC_TIMESTAMP() ORDER BY expires_at ASC LIMIT {$limit}");
$stmt->execute();
$subs = $stmt->fetchAll();

$ok = 0;
$failed = 0;

foreach ($subs as $sub) {
    echo "Expire user={$sub['user_id']} uuid={$sub['uuid']} expires={$sub['expires_at']}\n";
    if ($dryRun) {
        continue;
    }

    $results = [];
    if (!$noXray) {
        $results = remove_vless_user_everywhere((string)$sub['uuid'], true);
        $bad = array_filter($results, fn($r) => empty($r['ok']));
        if ($bad) {
            $failed++;
            echo "  Xray errors: " . json_encode($bad, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        }
    }

    db()->prepare('UPDATE subscriptions SET active=0 WHERE id=?')->execute([$sub['id']]);
    try {
        db()->prepare('INSERT INTO admin_logs (admin_user_id, action, target_user_id, details) VALUES (NULL, ?, ?, ?)')
            ->execute(['subscription_expired', $sub['user_id'], json_encode(['uuid'=>$sub['uuid'], 'xray'=>$results], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    } catch (Throwable $e) {}
    $ok++;
}

echo "Done. Processed={$ok}, failed_xray={$failed}, dryRun=" . ($dryRun ? 'yes' : 'no') . "\n";
