<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Services/XrayProvision.php';

$uuid = uuid_v4();
$email = 'magicvpn_test_' . time();

echo "Test UUID: {$uuid}\n";
echo "Test email: {$email}\n\n";

$results = ensure_vless_user_everywhere($uuid, $email, false);
foreach ($results as $r) {
    echo ($r['ok'] ? 'OK' : 'FAIL') . ' add ' . ($r['server'] ?? '-') . ' mode=' . ($r['mode'] ?? '-') . "\n";
    if (!$r['ok']) echo ($r['error'] ?? '') . "\n";
}

echo "\nУдаляю тестового пользователя через API...\n";
$results = remove_vless_user_everywhere_by_email($uuid, $email, false);
foreach ($results as $r) {
    echo ($r['ok'] ? 'OK' : 'FAIL') . ' remove ' . ($r['server'] ?? '-') . ' mode=' . ($r['mode'] ?? '-') . "\n";
    if (!$r['ok']) echo ($r['error'] ?? '') . "\n";
}
