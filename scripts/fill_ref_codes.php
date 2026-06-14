<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
init_db();
$rows = db()->query("SELECT * FROM users WHERE ref_code IS NULL OR ref_code='' ORDER BY id ASC")->fetchAll();
foreach ($rows as $u) {
    $code = user_ref_code($u);
    echo "user={$u['id']} ref={$code}" . PHP_EOL;
}
echo "Done: " . count($rows) . PHP_EOL;
