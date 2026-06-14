<?php
$providers = $providers ?? [];
$balance = (int)($balance ?? 0);
$planPrice = (int)($plan['price'] ?? 0);
$canPayBalance = $balance >= $planPrice;
?>
<style>
  .checkout-shell-v2{color:#f3f7ff}.wizard-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:22px}.wizard-step{border:1px solid rgba(130,170,255,.18);background:rgba(8,18,42,.72);border-radius:18px;padding:14px;min-height:92px;color:#eaf2ff}.wizard-step.active{border-color:rgba(88,166,255,.75);box-shadow:0 0 0 1px rgba(88,166,255,.18),0 16px 40px rgba(0,70,180,.18)}.wizard-step.done{border-color:rgba(80,255,180,.35);background:rgba(8,42,34,.45)}.wizard-step .n{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(88,166,255,.18);color:#dff0ff;font-weight:800;margin-bottom:10px}.wizard-step.active .n{background:linear-gradient(135deg,#1f8cff,#69e3ff);color:#03152c}.wizard-step.done .n{background:rgba(80,255,180,.18);color:#9affd0}.wizard-step b{display:block;font-size:14px}.wizard-step span{display:block;color:#9fb2d7;font-size:12px;margin-top:2px}.checkout-card{border:1px solid rgba(130,170,255,.18);background:linear-gradient(180deg,rgba(13,29,64,.96),rgba(7,14,34,.96));border-radius:28px;color:#fff;box-shadow:0 18px 60px rgba(0,0,0,.22)}.checkout-sticky{top:18px}.checkout-price{font-size:44px;line-height:1;font-weight:900;letter-spacing:-.04em}.price-old{color:#7f91b5;text-decoration:line-through}.text-muted-blue{color:#a7b8dc!important}.badge-soft{background:rgba(89,166,255,.14);color:#bfe4ff;border:1px solid rgba(89,166,255,.22)}.checkout-benefits{display:grid;gap:10px;color:#c9d8f6}.checkout-benefits span{color:#69e3ff;font-weight:900}.payment-methods{display:grid;gap:12px}.payment-method{display:block;cursor:pointer}.payment-method input{position:absolute;opacity:0;pointer-events:none}.payment-card-inner{display:flex;gap:14px;align-items:flex-start;border:1px solid rgba(130,170,255,.18);border-radius:20px;padding:16px;background:rgba(255,255,255,.035);transition:.18s}.payment-method input:checked + .payment-card-inner{border-color:rgba(105,227,255,.8);box-shadow:0 0 0 1px rgba(105,227,255,.16);background:rgba(35,135,255,.10)}.payment-dot{width:20px;height:20px;border-radius:50%;border:2px solid rgba(160,190,230,.55);margin-top:2px;flex:0 0 auto}.payment-method input:checked + .payment-card-inner .payment-dot{border:6px solid #69e3ff}.payment-card-inner strong{display:block;color:#fff}.payment-card-inner small{display:block;color:#a7b8dc;margin-top:2px}.btn-magic{background:linear-gradient(135deg,#2188ff,#65e2ff);border:0;color:#03152c;font-weight:800}.btn-outline-magic{border:1px solid rgba(130,170,255,.28);color:#d7e8ff;background:rgba(255,255,255,.03)}.btn-outline-magic:hover{color:#fff;border-color:rgba(105,227,255,.7)}.next-preview{border:1px solid rgba(130,170,255,.16);background:rgba(10,22,49,.62);border-radius:22px;padding:18px;color:#eaf2ff}.next-preview b{color:#fff}.next-preview .item{display:flex;gap:12px;margin-top:12px}.next-preview .icon{width:34px;height:34px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(88,166,255,.13)}
  @media(max-width:991px){.wizard-steps{grid-template-columns:repeat(2,minmax(0,1fr))}.checkout-price{font-size:36px}}
  @media(max-width:575px){.wizard-steps{grid-template-columns:1fr}}
</style>

<section class="checkout-shell-v2">
  <div class="wizard-steps">
    <div class="wizard-step done"><div class="n">✓</div><b>1. Выбор</b><span><?= e($plan['title']) ?></span></div>
    <div class="wizard-step active"><div class="n">2</div><b>2. Оплата</b><span>Выберите способ оплаты</span></div>
    <div class="wizard-step"><div class="n">3</div><b>3. V2RayTun</b><span>Ссылка на приложение</span></div>
    <div class="wizard-step"><div class="n">4</div><b>4. Ключ</b><span>Автоимпорт профиля</span></div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="checkout-card p-4 p-lg-5 sticky-lg-top checkout-sticky">
        <span class="badge badge-soft rounded-pill mb-3">Оплата тарифа</span>
        <h1 class="h2 fw-black mb-3"><?= e($plan['title']) ?></h1>
        <div class="checkout-price mb-2"><?= (int)$plan['price'] ?> ₽</div>
        <?php if (!empty($plan['old_price'])): ?><div class="price-old mb-3"><?= (int)$plan['old_price'] ?> ₽</div><?php endif; ?>
        <div class="text-muted-blue mb-2">Срок доступа: <b class="text-white"><?= (int)$plan['days'] ?> дней</b></div>
        <div class="text-muted-blue mb-4">Ваш баланс: <b class="text-white"><?= e((string)$balance) ?> ₽</b></div>
        <div class="checkout-benefits mb-4">
          <div><span>✓</span> Все активные локации MagicVPN</div>
          <div><span>✓</span> Подписка активируется после оплаты</div>
          <div><span>✓</span> После оплаты откроется шаг установки V2RayTun</div>
          <div><span>✓</span> На последнем шаге будет автоимпорт ключа</div>
        </div>
        <a href="/buy" class="btn btn-outline-magic w-100">← Назад к выбору тарифа</a>
      </div>
    </div>

    <div class="col-lg-7">
      <form method="post" class="checkout-card p-4 p-lg-5 mb-4">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
        <span class="badge badge-soft rounded-pill mb-2">Шаг 2 из 4</span>
        <h2 class="h3 fw-bold mb-1">Выберите способ оплаты</h2>
        <div class="text-muted-blue mb-4">Если оплатите с баланса, сразу перейдёте к шагам 3–4. При ручной оплате ключ откроется после подтверждения админом.</div>

        <div class="payment-methods mb-4">
          <label class="payment-method <?= $canPayBalance ? '' : 'opacity-75' ?>">
            <input type="radio" name="provider" value="balance" <?= $canPayBalance ? 'checked' : 'disabled' ?> required>
            <span class="payment-card-inner">
              <span class="payment-dot"></span>
              <span>
                <strong>Оплатить с баланса</strong>
                <small><?= $canPayBalance ? 'Списание произойдёт сразу, подписка активируется автоматически' : 'Недостаточно средств. Пополните баланс, чтобы оплатить без ожидания' ?></small>
              </span>
            </span>
          </label>

          <?php foreach ($providers as $i => $pr): ?>
            <?php $code = (string)$pr['code']; ?>
            <label class="payment-method">
              <input type="radio" name="provider" value="<?= e($code) ?>" <?= (!$canPayBalance && $i === 0) ? 'checked' : '' ?> required>
              <span class="payment-card-inner">
                <span class="payment-dot"></span>
                <span>
                  <strong><?= e($pr['title']) ?></strong>
                  <small><?= $code === 'manual' ? 'После оплаты загрузите чек, админ подтвердит заявку' : 'Онлайн-оплата через платёжный провайдер' ?></small>
                </span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>

        <?php if (empty($providers) && !$canPayBalance): ?>
          <div class="alert alert-warning rounded-4">Активные способы оплаты пока не настроены, а средств на балансе недостаточно.</div>
          <a class="btn btn-magic btn-lg w-100" href="/balance">Пополнить баланс</a>
        <?php else: ?>
          <button class="btn btn-magic btn-lg w-100">Продолжить оплату</button>
          <?php if(!$canPayBalance): ?><a class="btn btn-outline-magic w-100 mt-2" href="/balance">Пополнить баланс</a><?php endif; ?>
        <?php endif; ?>
      </form>

      <div class="next-preview">
        <b>Что будет дальше?</b>
        <div class="item"><div class="icon">📲</div><div><b>Шаг 3:</b> покажем ссылки на V2RayTun для Android/iOS и официальный сайт.</div></div>
        <div class="item"><div class="icon">🔗</div><div><b>Шаг 4:</b> покажем subscription-ссылку, VLESS-ключи, QR и кнопку автоимпорта.</div></div>
      </div>
    </div>
  </div>
</section>
