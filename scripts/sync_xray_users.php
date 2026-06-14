<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Services/VpnLinks.php';
require __DIR__ . '/../app/Services/XrayProvision.php';
init_db();
$dry = in_array('--dry-run', $argv, true);
$enqueue = in_array('--enqueue', $argv, true);
$rows = db()->query("SELECT user_id, uuid FROM subscriptions WHERE server_id='global' AND active=1 AND expires_at > UTC_TIMESTAMP() ORDER BY user_id ASC")->fetchAll();
if ($dry) { echo "Active users to sync: " . count($rows) . PHP_EOL; exit; }
if ($enqueue) {
    $count = enqueue_sync_all_active_users();
    echo "Enqueued sync jobs: {$count}" . PHP_EOL;
    exit;
}
$ok=0; $fail=0;
foreach ($rows as $r) {
    try {
        $results = ensure_vless_user_everywhere((string)$r['uuid'], 'web_' . (int)$r['user_id']);
        $failed = array_filter($results, fn($x)=>empty($x['ok']));
        if ($failed) throw new RuntimeException(json_encode(array_values($failed), JSON_UNESCAPED_UNICODE));
        $ok++;
        echo "OK user=" . (int)$r['user_id'] . PHP_EOL;
    } catch (Throwable $e) {
        $fail++;
        echo "FAIL user=" . (int)$r['user_id'] . ': ' . $e->getMessage() . PHP_EOL;
    }
}
echo "Done. OK={$ok} FAIL={$fail}" . PHP_EOL;
