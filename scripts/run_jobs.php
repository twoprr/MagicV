<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Services/VpnLinks.php';
require __DIR__ . '/../app/Services/XrayProvision.php';
init_db();
$limit = 5;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--limit=')) $limit = max(1, (int)substr($arg, 8));
}
$res = run_jobs($limit);
echo '[' . gmdate('Y-m-d H:i:s') . '] processed=' . $res['processed'] . ' done=' . $res['done'] . ' failed=' . $res['failed'] . PHP_EOL;
