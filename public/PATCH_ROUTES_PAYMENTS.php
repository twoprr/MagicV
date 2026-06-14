<?php
// Вставить/адаптировать эти маршруты в public/index.php твоего сайта.

require_once __DIR__ . '/../app/Services/PlanService.php';
require_once __DIR__ . '/../app/Services/PaymentService.php';
require_once __DIR__ . '/../app/Services/SubscriptionService.php';

if ($path === '/buy') {
    require_login();
    $plans = PlanService::activePlans();
    return view('user/buy_plans', compact('plans'));
}

if ($path === '/checkout') {
    require_login();
    $plan = PlanService::find((int)($_GET['plan'] ?? 0));
    if (!$plan || !(int)$plan['is_active']) { http_response_code(404); exit('Тариф не найден'); }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $provider = $_POST['provider'] ?? '';
        $res = PaymentService::createPayment((int)current_user()['id'], $plan, $provider);
        header('Location: ' . $res['pay_url']);
        exit;
    }
    $providers = PaymentService::activeProviders();
    return view('user/checkout', compact('plan', 'providers'));
}

if ($path === '/admin/plans') {
    require_admin();
    $plans = PlanService::allPlans();
    return view('admin/plans', compact('plans'));
}

if ($path === '/admin/plans/edit') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $plan = $id ? PlanService::find($id) : null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        PlanService::save($_POST, $id ?: null);
        header('Location: /admin/plans');
        exit;
    }
    return view('admin/plan_edit', compact('plan'));
}

if ($path === '/admin/payments') {
    require_admin();
    $payments = db()->query("SELECT p.*, u.email FROM payments p LEFT JOIN users u ON u.id=p.user_id ORDER BY p.id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
    return view('admin/payments', compact('payments'));
}

if ($path === '/admin/payment-providers') {
    require_admin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        PaymentService::saveProvider($_POST['code'], $_POST);
        header('Location: /admin/payment-providers');
        exit;
    }
    $providers = PaymentService::allProviders();
    return view('admin/payment_providers', compact('providers'));
}

if ($path === '/webhook/aaio') {
    $raw = file_get_contents('php://input');
    $data = $_POST ?: [];
    $provider = PaymentService::provider('aaio');
    $cfg = json_decode($provider['config_json'] ?? '{}', true) ?: [];
    $secret = $cfg['secret_key'] ?? '';
    $paymentId = (int)($data['order_id'] ?? 0);
    $amount = (string)($data['amount'] ?? '');
    $currency = (string)($data['currency'] ?? '');
    $merchant = (string)($data['merchant_id'] ?? '');
    $sign = (string)($data['sign'] ?? '');
    $expected = hash('sha256', implode(':', [$merchant, $amount, $currency, $secret, $paymentId]));
    if (!$paymentId || !hash_equals($expected, $sign)) { http_response_code(403); exit('bad sign'); }
    PaymentService::markPaid($paymentId, $raw ?: json_encode($data, JSON_UNESCAPED_UNICODE));
    echo 'OK'; exit;
}

if ($path === '/webhook/cryptocloud') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;
    $paymentId = (int)($data['order_id'] ?? $data['invoice_id'] ?? 0);
    $status = strtolower((string)($data['status'] ?? ''));
    if (!$paymentId) { http_response_code(400); exit('no order'); }
    if (in_array($status, ['paid', 'success', 'overpaid'], true)) {
        PaymentService::markPaid($paymentId, $raw);
    }
    echo 'OK'; exit;
}
