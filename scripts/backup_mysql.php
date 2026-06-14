<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$db = require __DIR__ . '/../config/database.php';
$backup = app_config('backup') ?: [];
$dir = (string)($backup['dir'] ?? '/var/backups/magicvpn');
$keepDays = max(1, (int)($backup['keep_days'] ?? 14));
if (!is_dir($dir)) mkdir($dir, 0700, true);
$ts = gmdate('Ymd_His');
$file = rtrim($dir, '/') . "/magicvpn_{$ts}.sql.gz";
$cmd = sprintf(
    'mysqldump --single-transaction --quick -h%s -P%d -u%s -p%s %s | gzip > %s',
    escapeshellarg($db['host'] ?? '127.0.0.1'),
    (int)($db['port'] ?? 3306),
    escapeshellarg($db['username'] ?? ''),
    escapeshellarg($db['password'] ?? ''),
    escapeshellarg($db['database'] ?? 'magicvpn'),
    escapeshellarg($file)
);
exec($cmd, $out, $code);
if ($code !== 0 || !file_exists($file)) {
    fwrite(STDERR, "Backup failed\n"); exit(1);
}
foreach (glob(rtrim($dir, '/') . '/magicvpn_*.sql.gz') ?: [] as $old) {
    if (filemtime($old) < time() - $keepDays * 86400) @unlink($old);
}
echo "Backup created: {$file}\n";
