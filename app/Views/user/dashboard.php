<?php
$balance = (int)($balance ?? 0);
$hasSub = !empty($sub);
$isActive = $hasSub && !empty($sub['active']) && strtotime((string)$sub['expires_at']) > time();
$expiresTs = $hasSub ? strtotime((string)$sub['expires_at']) : 0;
$daysLeft = $isActive ? max(0, (int)ceil(($expiresTs - time()) / 86400)) : 0;
$hoursLeft = $isActive ? max(0, (int)ceil(($expiresTs - time()) / 3600)) : 0;
$progress = $isActive ? max(8, min(100, $daysLeft >= 30 ? 100 : (int)round(($daysLeft / 30) * 100))) : 0;
$profileCount = is_array($links ?? null) ? count($links) : 0;
?>

<section class="cabinet-hero magic-card p-4 p-lg-5 mb-4 overflow-hidden position-relative">
  <div class="row g-4 align-items-center position-relative">
    <div class="col-lg-7">
      <span class="badge badge-soft rounded-pill mb-3">Личный кабинет</span>
      <h1 class="display-5 fw-black mb-3">Управление MagicVPN</h1>
      <p class="lead text-muted-blue mb-4">Здесь ваши ключи, subscription-ссылка, срок подписки, поддержка и реферальная программа.</p>
      <div class="d-flex gap-2 flex-wrap">
        <a href="/buy" class="btn btn-magic">Купить / продлить</a>
        <a href="/support" class="btn btn-outline-magic">Поддержка</a>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="subscription-widget">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
          <div>
            <div class="text-muted-blue small">Статус подписки</div>
            <div class="h3 fw-bold mb-0"><?= $isActive ? 'Активна' : 'Неактивна' ?></div>
          </div>
          <span class="status-pill <?= $isActive ? '' : 'off' ?>"><?= $isActive ? '✅ Online' : '⛔ Offline' ?></span>
        </div>
        <?php if ($hasSub): ?>
          <div class="sub-progress mb-3"><span style="width: <?= (int)$progress ?>%"></span></div>
          <div class="row g-2">
            <div class="col-6"><div class="mini-stat"><b><?= $isActive ? (string)$daysLeft : '0' ?></b><span>дней осталось</span></div></div>
            <div class="col-6"><div class="mini-stat"><b><?= (string)$profileCount ?></b><span>VPN-профилей</span></div></div>
          </div>
          <div class="mt-3 text-muted-blue small">Действует до: <b class="text-white"><?= e(date('d.m.Y H:i', $expiresTs)) ?> UTC</b></div>
        <?php else: ?>
          <div class="text-muted-blue">Активной подписки пока нет. Можно купить тариф или взять тестовый доступ.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<div class="row g-4 mb-4">
  <div class="col-md-3">
    <a class="info-tile h-100 text-decoration-none d-block" href="/balance">
      <div class="info-icon">💰</div>
      <div>
        <div class="text-muted-blue small">Баланс</div>
        <div class="h5 mb-1"><?= e((string)$balance) ?> ₽</div>
        <div class="text-muted-blue small">Пополнить или посмотреть историю.</div>
      </div>
    </a>
  </div>
  <div class="col-md-3">
    <div class="info-tile h-100">
      <div class="info-icon">🔐</div>
      <div>
        <div class="text-muted-blue small">Доступ</div>
        <div class="h5 mb-1">Все активные локации</div>
        <div class="text-muted-blue small">Одна подписка открывает все доступные серверы.</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="info-tile h-100">
      <div class="info-icon">⚡</div>
      <div>
        <div class="text-muted-blue small">Настройка</div>
        <div class="h5 mb-1">Включите Mux</div>
        <div class="text-muted-blue small">Для стабильности включите Mux, остальное лучше не менять.</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="info-tile h-100">
      <div class="info-icon">🎁</div>
      <div>
        <div class="text-muted-blue small">Рефералы</div>
        <div class="h5 mb-1"><?= e((string)($refCount ?? 0)) ?> приглашено</div>
        <div class="text-muted-blue small">После оплаты приглашённого пользователя начисляются бонусные дни.</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="magic-card p-4 h-100">
      <h2 class="h4 fw-bold mb-3">Быстрые действия</h2>
      <div class="d-grid gap-2">
        <a href="/buy" class="btn btn-magic">Продлить подписку</a>
        <a href="/balance" class="btn btn-outline-magic">Баланс и пополнение</a>
        <?php if(!$hasSub && empty($user['trial_used'])): ?>
          <form method="post" action="/trial">
            <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
            <button class="btn btn-outline-magic w-100">Тест на 24 часа</button>
          </form>
        <?php endif; ?>
        <a href="/status" class="btn btn-outline-magic">Статус серверов</a>
        <a href="/support" class="btn btn-outline-magic">Написать в поддержку</a>
      </div>

      <?php if(!empty($refLink)): ?>
      <div class="compact-panel mt-3">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
          <h5 class="mb-0">Реферальная ссылка</h5>
          <button class="btn btn-outline-magic btn-sm" onclick="copyText('refLink')">Скопировать</button>
        </div>
        <p class="text-muted-blue small mb-2">Отправьте другу. Бонус начислится после его оплаты.</p>
        <div id="refLink" class="copy-box compact-copy small"><?=e($refLink)?></div>
      </div>
      <?php endif; ?>

      <div class="compact-panel mt-3">
        <h5 class="mb-2">Инструкция</h5>
        <div class="instruction-mini">
          <span>1. Скопируйте subscription</span>
          <span>2. Добавьте в приложение</span>
          <span>3. Включите Mux</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="magic-card p-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
          <span class="badge badge-soft rounded-pill mb-2">VPN-профили</span>
          <h2 class="fw-bold mb-1">Ключи и подписка</h2>
          <div class="text-muted-blue">Используйте subscription-ссылку для автоматического обновления серверов.</div>
        </div>
        <a href="/buy" class="btn btn-outline-magic">Продлить</a>
      </div>

      <?php if(!$isActive): ?>
        <div class="empty-state">
          <div class="empty-icon">🔒</div>
          <h3 class="h4">Ключи пока недоступны</h3>
          <p class="text-muted-blue mb-4">Ключи появятся после активации подписки или тестового доступа.</p>
          <a href="/buy" class="btn btn-magic">Выбрать тариф</a>
        </div>
      <?php else: ?>
        <?php if(!empty($subUrl)): ?>
          <div class="sub-link-card mb-4">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-2">
              <div>
                <h5 class="mb-1">Единая subscription-ссылка</h5>
                <div class="text-muted-blue small">Лучший вариант для v2rayNG, Hiddify, Streisand и похожих приложений.</div>
              </div>
              <button class="btn btn-magic btn-sm" onclick="copyText('subUrl')">Скопировать</button>
            </div>
            <div id="subUrl" class="copy-box"><?=e($subUrl)?></div>
          </div>
        <?php endif; ?>

        <?php foreach($links as $i=>$l): ?>
          <div class="vpn-profile-card mb-3">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
              <div>
                <div class="text-muted-blue small">Профиль #<?= (int)$i + 1 ?></div>
                <h5 class="mb-0"><?=e($l['remark'])?></h5>
              </div>
              <button class="btn btn-outline-magic btn-sm" onclick="copyText('link<?=$i?>')">Скопировать VLESS</button>
            </div>
            <div id="link<?=$i?>" class="copy-box"><?=e($l['link'])?></div>
            <div class="d-flex align-items-center gap-3 flex-wrap mt-3">
              <canvas class="qr-box" data-qr="<?=e($l['link'])?>"></canvas>
              <div class="text-muted-blue small">Можно отсканировать QR-код или скопировать ссылку вручную.</div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
