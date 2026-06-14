<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$layout = $root . '/app/Views/layouts/main.php';

if (!is_file($layout)) {
    echo "SKIP: layout not found: {$layout}\n";
    exit(0);
}

$s = file_get_contents($layout);
if (str_contains($s, 'home_live_patch.css')) {
    echo "OK: home_live_patch.css already connected\n";
    exit(0);
}

$bak = $layout . '.bak-home-live-' . date('YmdHis');
copy($layout, $bak);

$line = '<link href="/assets/css/home_live_patch.css" rel="stylesheet">';
if (str_contains($s, '<link href="/assets/css/location_patch.css" rel="stylesheet">')) {
    $s = str_replace(
        '<link href="/assets/css/location_patch.css" rel="stylesheet">',
        '<link href="/assets/css/location_patch.css" rel="stylesheet">' . "\n  " . $line,
        $s
    );
} elseif (str_contains($s, '<link href="/assets/css/home_legal_patch.css" rel="stylesheet">')) {
    $s = str_replace(
        '<link href="/assets/css/home_legal_patch.css" rel="stylesheet">',
        '<link href="/assets/css/home_legal_patch.css" rel="stylesheet">' . "\n  " . $line,
        $s
    );
} elseif (str_contains($s, '<link href="/assets/css/style.css" rel="stylesheet">')) {
    $s = str_replace(
        '<link href="/assets/css/style.css" rel="stylesheet">',
        '<link href="/assets/css/style.css" rel="stylesheet">' . "\n  " . $line,
        $s
    );
} else {
    echo "WARNING: CSS link place not found. Add manually in layout head: {$line}\n";
    exit(0);
}

file_put_contents($layout, $s);
echo "PATCHED: {$layout}\nBACKUP: {$bak}\n";
