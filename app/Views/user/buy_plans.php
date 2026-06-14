<?php
$plans = $plans ?? [];
?>
<style>
  .wizard-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:22px}
  .wizard-step{border:1px solid rgba(130,170,255,.18);background:rgba(8,18,42,.72);border-radius:18px;padding:14px;min-height:92px;color:#eaf2ff;position:relative;overflow:hidden}
  .wizard-step.active{border-color:rgba(88,166,255,.75);box-shadow:0 0 0 1px rgba(88,166,255,.18),0 16px 40px rgba(0,70,180,.18)}
  .wizard-step .n{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(88,166,255,.18);color:#dff0ff;font-weight:800;margin-bottom:10px}
  .wizard-step.active .n{background:linear-gradient(135deg,#1f8cff,#69e3ff);color:#03152c}
  .wizard-step b{display:block;font-size:14px}.wizard-step span{display:block;color:#9fb2d7;font-size:12px;margin-top:2px}
  .buy-hero-v2{background:radial-gradient(circle at 15% 15%,rgba(57,130,255,.32),transparent 34%),radial-gradient(circle at 90% 10%,rgba(91,226,255,.18),transparent 28%),linear-gradient(135deg,rgba(7,19,46,.98),rgba(7,13,31,.96));border:1px solid rgba(130,170,255,.18);border-radius:28px;color:#fff}
  .pricing-card{position:relative;display:flex;flex-direction:column;border:1px solid rgba(130,170,255,.18);background:linear-gradient(180deg,rgba(13,29,64,.96),rgba(7,14,34,.96));border-radius:26px;padding:26px;color:#f3f7ff;box-shadow:0 18px 60px rgba(0,0,0,.22);min-height:100%}
  .pricing-card.popular{border-color:rgba(70,205,255,.75);box-shadow:0 22px 70px rgba(0,112,255,.24)}
  .popular-ribbon{position:absolute;right:18px;top:18px;background:linear-gradient(135deg,#1f8cff,#69e3ff);color:#03152c;font-weight:900;border-radius:999px;padding:7px 11px;font-size:12px}
  .price-main{font-size:38px;font-weight:900;letter-spacing:-.04em}.price-old{color:#7f91b5;text-decoration:line-through;margin-left:8px}
  .plan-checks{list-style:none;padding:0;margin:18px 0 22px;display:grid;gap:10px;color:#c9d8f6;font-size:14px}.plan-checks li:before{content:'✓';color:#69e3ff;font-weight:900;margin-right:8px}
  .text-muted-blue{color:#a7b8dc!important}.badge-soft{background:rgba(89,166,255,.14);color:#bfe4ff;border:1px solid rgba(89,166,255,.22)}
  .btn-magic{background:linear-gradient(135deg,#2188ff,#65e2ff);border:0;color:#03152c;font-weight:800}.btn-outline-magic{border:1px solid rgba(130,170,255,.28);color:#d7e8ff;background:rgba(255,255,255,.03)}.btn-outline-magic:hover{color:#fff;border-color:rgba(105,227,255,.7)}
  .mini-guide{border:1px solid rgba(130,170,255,.16);background:rgba(10,22,49,.62);border-radius:22px;padding:18px;color:#eaf2ff;height:100%}.mini-guide .icon{font-size:24px;margin-bottom:8px}
  @media(max-width:991px){.wizard-steps{grid-template-columns:repeat(2,minmax(0,1fr))}.price-main{font-size:32px}}
  @media(max-width:575px){.wizard-steps{grid-template-columns:1fr}}
</style>

<section class="buy-hero-v2 p-4 p-lg-5 mb-4 overflow-hidden position-relative">
  <div class="row g-4 align-items-center position-relative">
    <div class="col-lg-8">
      <span class="badge badge-soft rounded-pill mb-3">Шаг 1 из 4</span>
      <h1 class="display-5 fw-black mb-3">Выберите тариф MagicVPN</h1>
      <p class="lead text-muted-blue mb-0">После оплаты вы сразу перейдёте к установке V2RayTun и получите subscription-ссылку с автоимпортом.</p>
    </div>
    <div class="col-lg-4">
      <div class="mini-guide">
        <div class="icon">🚀</div>
        <b>Быстрый старт</b>
        <div class="text-muted-blue small mt-1">Выбор → оплата → установка приложения → автоимпорт ключа.</div>
      </div>
    </div>
  </div>
</section>

<div class="wizard-steps">
  <div class="wizard-step active"><div class="n">1</div><b>Выбор</b><span>Подберите срок доступа</span></div>
  <div class="wizard-step"><div class="n">2</div><b>Оплата</b><span>Баланс или способ оплаты</span></div>
  <div class="wizard-step"><div class="n">3</div><b>V2RayTun</b><span>Установите приложение</span></div>
  <div class="wizard-step"><div class="n">4</div><b>Ключ</b><span>Импортируйте подключение</span></div>
</div>

<?php if (empty($plans)): ?>
  <div class="magic-card p-5 text-center">
    <div class="empty-icon mx-auto mb-3">💳</div>
    <h2 class="h4">Тарифы пока не настроены</h2>
    <p class="text-muted-blue mb-0">Администратор может добавить тарифы в разделе /admin/plans.</p>
  </div>
<?php else: ?>
  <div class="row g-4 align-items-stretch">
    <?php foreach ($plans as $p): ?>
      <?php $popular = !empty($p['is_popular']); ?>
      <div class="col-md-6 col-xl-3">
        <div class="pricing-card <?= $popular ? 'popular' : '' ?>">
          <?php if ($popular): ?><div class="popular-ribbon">Популярный</div><?php endif; ?>
          <?php if (!empty($p['badge'])): ?><span class="badge badge-soft rounded-pill mb-3 align-self-start"><?= e($p['badge']) ?></span><?php endif; ?>
          <h2 class="h4 fw-bold mb-2"><?= e($p['title']) ?></h2>
          <div class="text-muted-blue small mb-3"><?= (int)$p['days'] ?> дней доступа ко всем активным локациям</div>
          <div class="mb-2">
            <span class="price-main"><?= (int)$p['price'] ?> ₽</span>
            <?php if (!empty($p['old_price'])): ?><span class="price-old"><?= (int)$p['old_price'] ?> ₽</span><?php endif; ?>
          </div>
          <p class="text-muted-blue small mb-0"><?= e($p['description'] ?? 'Подходит для стабильного ежедневного подключения.') ?></p>
          <ul class="plan-checks">
            <li>Все активные страны</li>
            <li>VLESS Reality</li>
            <li>Subscription URL</li>
            <li>Автоимпорт в V2RayTun</li>
          </ul>
          <a class="btn <?= $popular ? 'btn-magic' : 'btn-outline-magic' ?> w-100 mt-auto" href="/checkout?plan=<?= (int)$p['id'] ?>">Выбрать и перейти к оплате</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4 mt-1">
    <div class="col-lg-4"><div class="mini-guide"><div class="icon">📲</div><b>Установка приложения</b><div class="text-muted-blue small mt-1">После оплаты покажем ссылки на V2RayTun для Android, iOS и официальный сайт.</div></div></div>
    <div class="col-lg-4"><div class="mini-guide"><div class="icon">🔗</div><b>Автоимпорт</b><div class="text-muted-blue small mt-1">Кнопка откроет V2RayTun и передаст subscription-ссылку.</div></div></div>
    <div class="col-lg-4"><div class="mini-guide"><div class="icon">🛟</div><b>Поддержка</b><div class="text-muted-blue small mt-1">Если импорт не сработал, можно скопировать ключ вручную или написать в поддержку.</div></div></div>
  </div>
<?php endif; ?>
