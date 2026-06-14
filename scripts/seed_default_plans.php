<?php
require __DIR__ . '/../app/bootstrap.php';
init_db();
$pdo = db();
$plans = [
    ['7 дней', 7, 199, null, 'Старт', 'Короткий доступ для проверки сервиса', 1, 0, 10],
    ['30 дней', 30, 499, null, 'Популярный', 'Оптимальный тариф на месяц', 1, 1, 20],
    ['90 дней', 90, 1199, 1499, 'Выгодно', 'Экономия при оплате на 3 месяца', 1, 0, 30],
    ['180 дней', 180, 1999, 2499, 'Максимум', 'Лучший вариант для постоянного использования', 1, 0, 40],
];
$stmt = $pdo->prepare("SELECT id FROM plans WHERE days = ? LIMIT 1");
$ins = $pdo->prepare("INSERT INTO plans (title, days, price, old_price, badge, description, is_active, is_popular, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($plans as $p) {
    $stmt->execute([$p[1]]);
    if (!$stmt->fetchColumn()) {
        $ins->execute($p);
        echo "Added plan {$p[0]}\n";
    }
}
$providers = [
    ['manual', 'Ручная оплата / чек', 1, json_encode(['instructions' => 'Оплатите переводом и загрузите чек.'], JSON_UNESCAPED_UNICODE)],
    ['cryptocloud', 'CryptoCloud', 0, json_encode(['api_key' => '', 'shop_id' => '', 'currency' => 'RUB'], JSON_UNESCAPED_UNICODE)],
    ['aaio', 'AAIO', 0, json_encode(['merchant_id' => '', 'secret_key' => '', 'currency' => 'RUB'], JSON_UNESCAPED_UNICODE)],
];
$sel = $pdo->prepare("SELECT id FROM payment_providers WHERE code = ? LIMIT 1");
$pin = $pdo->prepare("INSERT INTO payment_providers (code, title, is_active, config_json) VALUES (?, ?, ?, ?)");
foreach ($providers as $p) {
    $sel->execute([$p[0]]);
    if (!$sel->fetchColumn()) {
        $pin->execute($p);
        echo "Added provider {$p[0]}\n";
    }
}
echo "OK\n";
