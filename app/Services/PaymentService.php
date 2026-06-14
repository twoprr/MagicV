<?php

class PaymentService
{
    public static function activeProviders(): array
    {
        $stmt = db()->query("SELECT * FROM payment_providers WHERE is_active = 1 ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function allProviders(): array
    {
        $stmt = db()->query("SELECT * FROM payment_providers ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function provider(string $code): ?array
    {
        $stmt = db()->prepare("SELECT * FROM payment_providers WHERE code = ? LIMIT 1");
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function saveProvider(string $code, array $data): void
    {
        $cfg = $data['config_json'] ?? '{}';
        if (is_array($cfg)) {
            $cfg = json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        $stmt = db()->prepare("UPDATE payment_providers SET title=?, is_active=?, config_json=?, updated_at=NOW() WHERE code=?");
        $stmt->execute([
            trim($data['title'] ?? $code),
            !empty($data['is_active']) ? 1 : 0,
            $cfg,
            $code,
        ]);
    }

    public static function createPayment(int $userId, array $plan, string $providerCode): array
    {
        $provider = self::provider($providerCode);
        if (!$provider || !(int)$provider['is_active']) {
            throw new RuntimeException('Платёжный способ недоступен');
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, days, amount, status, plan_id, payment_provider, created_at) VALUES (?, ?, ?, 'pending', ?, ?, NOW())");
            $stmt->execute([$userId, (int)$plan['days'], (int)$plan['price'], (int)$plan['id'], $providerCode]);
            $orderId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO payments (user_id, order_id, plan_id, provider_code, amount, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$userId, $orderId, (int)$plan['id'], $providerCode, (int)$plan['price']]);
            $paymentId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare("UPDATE orders SET payment_id=? WHERE id=?");
            $stmt->execute([$paymentId, $orderId]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        if ($providerCode === 'manual') {
            return ['payment_id' => $paymentId, 'order_id' => $orderId, 'pay_url' => '/order?id=' . $orderId];
        }

        if ($providerCode === 'cryptocloud') {
            return self::createCryptoCloudInvoice($paymentId, $orderId, $plan, $provider);
        }

        if ($providerCode === 'aaio') {
            return self::createAaioInvoice($paymentId, $orderId, $plan, $provider);
        }

        throw new RuntimeException('Неизвестный платёжный провайдер');
    }

    private static function baseUrl(): string
    {
        $cfg = require __DIR__ . '/../../config/app.php';
        return rtrim($cfg['base_url'] ?? '', '/') ?: '';
    }

    private static function createCryptoCloudInvoice(int $paymentId, int $orderId, array $plan, array $provider): array
    {
        $cfg = json_decode($provider['config_json'] ?: '{}', true) ?: [];
        $apiKey = trim($cfg['api_key'] ?? '');
        $shopId = trim($cfg['shop_id'] ?? '');
        if (!$apiKey || !$shopId) {
            throw new RuntimeException('CryptoCloud не настроен');
        }
        $payload = [
            'shop_id' => $shopId,
            'amount' => (string)(int)$plan['price'],
            'currency' => $cfg['currency'] ?? 'RUB',
            'order_id' => (string)$paymentId,
            'email' => current_user()['email'] ?? '',
            'add_fields' => [
                'time_to_pay' => ['hours' => 1],
                'available_currencies' => ['USDT_TRC20', 'BTC', 'ETH'],
            ],
        ];
        $res = self::httpJson('https://api.cryptocloud.plus/v2/invoice/create', $payload, [
            'Authorization: Token ' . $apiKey,
        ]);
        $raw = json_encode($res, JSON_UNESCAPED_UNICODE);
        $payUrl = $res['result']['link'] ?? $res['pay_url'] ?? null;
        $external = $res['result']['uuid'] ?? null;
        db()->prepare("UPDATE payments SET external_id=?, pay_url=?, raw_response=? WHERE id=?")->execute([$external, $payUrl, $raw, $paymentId]);
        if (!$payUrl) {
            throw new RuntimeException('CryptoCloud не вернул ссылку оплаты');
        }
        return ['payment_id' => $paymentId, 'order_id' => $orderId, 'pay_url' => $payUrl];
    }

    private static function createAaioInvoice(int $paymentId, int $orderId, array $plan, array $provider): array
    {
        $cfg = json_decode($provider['config_json'] ?: '{}', true) ?: [];
        $merchant = trim($cfg['merchant_id'] ?? '');
        $secret = trim($cfg['secret_key'] ?? '');
        $currency = $cfg['currency'] ?? 'RUB';
        if (!$merchant || !$secret) {
            throw new RuntimeException('AAIO не настроен');
        }
        $amount = (string)(int)$plan['price'];
        $orderIdStr = (string)$paymentId;
        $sign = hash('sha256', implode(':', [$merchant, $amount, $currency, $secret, $orderIdStr]));
        $params = http_build_query([
            'merchant_id' => $merchant,
            'amount' => $amount,
            'currency' => $currency,
            'order_id' => $orderIdStr,
            'sign' => $sign,
            'desc' => 'MagicVPN ' . $plan['title'],
        ]);
        $payUrl = 'https://aaio.so/merchant/pay?' . $params;
        db()->prepare("UPDATE payments SET external_id=?, pay_url=? WHERE id=?")->execute([$orderIdStr, $payUrl, $paymentId]);
        return ['payment_id' => $paymentId, 'order_id' => $orderId, 'pay_url' => $payUrl];
    }

    private static function httpJson(string $url, array $payload, array $headers = []): array
    {
        $ch = curl_init($url);
        $headers[] = 'Content-Type: application/json';
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $body = curl_exec($ch);
        if ($body === false) {
            throw new RuntimeException(curl_error($ch));
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json = json_decode($body, true);
        if ($code < 200 || $code >= 300 || !is_array($json)) {
            throw new RuntimeException('HTTP ' . $code . ': ' . substr($body, 0, 500));
        }
        return $json;
    }

    public static function markPaid(int $paymentId, string $raw = ''): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT p.*, o.user_id AS order_user_id, o.days FROM payments p LEFT JOIN orders o ON o.id=p.order_id WHERE p.id=? FOR UPDATE");
            $stmt->execute([$paymentId]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$p) throw new RuntimeException('payment not found');
            if ($p['status'] === 'paid') {
                $pdo->commit();
                return;
            }
            $pdo->prepare("UPDATE payments SET status='paid', paid_at=NOW(), raw_request=? WHERE id=?")->execute([$raw, $paymentId]);
            if ($p['order_id']) {
                $pdo->prepare("UPDATE orders SET status='paid' WHERE id=?")->execute([$p['order_id']]);
            }
            $sub = SubscriptionService::activateOrExtend((int)$p['user_id'], (int)$p['days']);
            $uuid = (string)($sub['uuid'] ?? '');
            if ($uuid !== '' && function_exists('create_job')) {
                create_job('provision_user', ['user_id'=>(int)$p['user_id'], 'uuid'=>$uuid, 'xray_email'=>'web_' . (int)$p['user_id'], 'order_id'=>(int)($p['order_id'] ?? 0)], 5);
            }
            if (function_exists('maybe_reward_referrer')) {
                maybe_reward_referrer((int)$p['user_id']);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
