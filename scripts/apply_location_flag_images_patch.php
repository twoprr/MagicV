<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$home = $root . '/app/Views/home.php';
if (!is_file($home)) {
    fwrite(STDERR, "ERROR: app/Views/home.php not found\n");
    exit(1);
}

$src = file_get_contents($home);
if ($src === false) {
    fwrite(STDERR, "ERROR: cannot read app/Views/home.php\n");
    exit(1);
}

$backup = $home . '.bak-location-flags-' . date('YmdHis');
copy($home, $backup);

$flags = [
    '<img class="location-flag-img" src="/assets/img/flags/de.svg" alt="Германия" loading="lazy">',
    '<img class="location-flag-img" src="/assets/img/flags/fr.svg" alt="Франция" loading="lazy">',
];

$count = 0;
$updated = preg_replace_callback(
    '~<(?P<tag>div|span)(?P<attrs>[^>]*class="[^"]*\blocation-flag-wrap\b[^"]*"[^>]*)>.*?</(?P=tag)>~su',
    function (array $m) use (&$count, $flags): string {
        $flag = $flags[$count] ?? $flags[$count % count($flags)];
        $count++;
        return '<' . $m['tag'] . $m['attrs'] . '>' . $flag . '</' . $m['tag'] . '>';
    },
    $src
);

if ($updated === null) {
    fwrite(STDERR, "ERROR: regex failed while patching home.php\n");
    exit(1);
}

if ($count === 0) {
    fwrite(STDERR, "WARNING: location-flag-wrap not found in app/Views/home.php. CSS and SVG files were still installed.\n");
} else {
    file_put_contents($home, $updated);
    echo "OK: patched {$count} location-flag-wrap block(s) in app/Views/home.php\n";
    echo "Backup: {$backup}\n";
}

$cssCandidates = [
    $root . '/public/assets/css/app.css',
    $root . '/public/assets/css/style.css',
    $root . '/public/assets/app.css',
    $root . '/public/assets/style.css',
];
$cssFile = null;
foreach ($cssCandidates as $candidate) {
    if (is_file($candidate)) { $cssFile = $candidate; break; }
}
if ($cssFile === null) {
    $cssFile = $root . '/public/assets/css/app.css';
    if (!is_dir(dirname($cssFile))) mkdir(dirname($cssFile), 0775, true);
    file_put_contents($cssFile, "");
}
$css = file_get_contents($cssFile) ?: '';
$marker = '/* MagicVPN location flag images */';
$add = <<<'CSS'

/* MagicVPN location flag images */
.location-flag-wrap {
  width: 72px;
  height: 72px;
  min-width: 72px;
  border-radius: 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
  overflow: hidden;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.14);
  box-shadow: 0 16px 40px rgba(0,0,0,.28);
}
.location-flag-img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
  border-radius: 16px;
  box-shadow: 0 8px 22px rgba(0,0,0,.28);
}
@media (max-width: 576px) {
  .location-flag-wrap {
    width: 58px;
    height: 58px;
    min-width: 58px;
    border-radius: 18px;
    padding: 8px;
  }
  .location-flag-img {
    border-radius: 13px;
  }
}
CSS;
if (!str_contains($css, $marker)) {
    file_put_contents($cssFile, rtrim($css) . $add . "\n");
    echo "OK: CSS added to " . str_replace($root . '/', '', $cssFile) . "\n";
} else {
    echo "OK: CSS already exists\n";
}
