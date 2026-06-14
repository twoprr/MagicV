<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$index = $root . '/public/index.php';
$layout = $root . '/app/Views/layouts/main.php';
$css = $root . '/public/assets/css/style.css';

function patch_file(string $path, callable $patcher): void {
    if (!is_file($path)) {
        echo "SKIP missing: {$path}\n";
        return;
    }
    $src = file_get_contents($path);
    $new = $patcher($src);
    if ($new === $src) {
        echo "OK unchanged: {$path}\n";
        return;
    }
    $bak = $path . '.bak-home-terms-' . date('YmdHis');
    copy($path, $bak);
    file_put_contents($path, $new);
    echo "PATCHED: {$path}\nBACKUP: {$bak}\n";
}

patch_file($index, function (string $s): string {
    if (str_contains($s, "view('legal/terms'")) return $s;
    $route = "\n    if (\$path === '/terms') { view('legal/terms'); return; }\n";
    $needle = "    if (\$path === '/') { view('home'); return; }\n";
    if (str_contains($s, $needle)) return str_replace($needle, $needle . $route, $s);
    $needle2 = "if (\$path === '/') { view('home'); return; }\n";
    if (str_contains($s, $needle2)) return str_replace($needle2, $needle2 . trim($route) . "\n", $s);
    echo "WARNING: could not auto-insert /terms route. Add manually inside try block: if (\$path === '/terms') { view('legal/terms'); return; }\n";
    return $s;
});

patch_file($layout, function (string $s): string {
    if (!str_contains($s, 'home_legal_patch.css')) {
        $s = str_replace('<link href="/assets/css/style.css" rel="stylesheet">', '<link href="/assets/css/style.css" rel="stylesheet">' . "\n  " . '<link href="/assets/css/home_legal_patch.css" rel="stylesheet">', $s);
    }
    if (!str_contains($s, 'href="/terms"')) {
        $s = str_replace('<li class="nav-item"><a class="nav-link" href="/status">Статус</a></li>', '<li class="nav-item"><a class="nav-link" href="/status">Статус</a></li>' . "\n        " . '<li class="nav-item"><a class="nav-link" href="/terms">Соглашение</a></li>', $s);
    }
    if (!str_contains($s, 'footer-social-links')) {
        $old = '<div class="col-md-6 text-md-end text-muted-blue">© <?=date(\'Y\')?> MagicVPN. Все права защищены.</div>';
        $new = '<div class="col-md-6 text-md-end text-muted-blue">' . "\n" .
            '        <div class="footer-social-links mb-2">' . "\n" .
            '          <a href="/terms">Пользовательское соглашение</a>' . "\n" .
            '          <span class="mx-2">·</span>' . "\n" .
            '          <a href="https://t.me/magicvpn_support" target="_blank" rel="noopener noreferrer">Telegram</a>' . "\n" .
            '          <span class="mx-2">·</span>' . "\n" .
            '          <a href="https://vk.com/magicvpn" target="_blank" rel="noopener noreferrer">VK</a>' . "\n" .
            '        </div>' . "\n" .
            '        © <?=date(\'Y\')?> MagicVPN. Все права защищены.' . "\n" .
            '      </div>';
        if (str_contains($s, $old)) $s = str_replace($old, $new, $s);
        else echo "WARNING: footer pattern not found, footer links were not inserted. Main page still contains links.\n";
    }
    return $s;
});

if (is_file($css)) {
    $extra = "\n.footer-social-links a{color:#9eefff;text-decoration:none}.footer-social-links a:hover{color:#fff;text-decoration:underline}\n";
    $current = file_get_contents($css);
    if (!str_contains($current, 'footer-social-links')) {
        file_put_contents($css, $current . $extra);
        echo "PATCHED: {$css}\n";
    }
}

echo "DONE\n";
