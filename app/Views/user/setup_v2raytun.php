<?php
$links = $links ?? [];
$subUrl = (string)($subUrl ?? '');
$autoImportUrl = (string)($autoImportUrl ?? ('v2raytun://import/' . $subUrl));
$androidUrl = 'https://play.google.com/store/apps/details?id=com.v2raytun.android';
$iosUrl = 'https://apps.apple.com/app/v2raytun/id6476628951';
$officialUrl = 'https://v2raytun.com/';
$expiresTs = !empty($sub['expires_at']) ? strtotime((string)$sub['expires_at']) : 0;
?>
<style>
  .setup-shell{color:#f3f7ff}.wizard-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:22px}.wizard-step{border:1px solid rgba(130,170,255,.18);background:rgba(8,18,42,.72);border-radius:18px;padding:14px;min-height:92px;color:#eaf2ff}.wizard-step.active{border-color:rgba(88,166,255,.75);box-shadow:0 0 0 1px rgba(88,166,255,.18),0 16px 40px rgba(0,70,180,.18)}.wizard-step.done{border-color:rgba(80,255,180,.35);background:rgba(8,42,34,.45)}.wizard-step .n{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(88,166,255,.18);color:#dff0ff;font-weight:800;margin-bottom:10px}.wizard-step.active .n{background:linear-gradient(135deg,#1f8cff,#69e3ff);color:#03152c}.wizard-step.done .n{background:rgba(80,255,180,.18);color:#9affd0}.wizard-step b{display:block;font-size:14px}.wizard-step span{display:block;color:#9fb2d7;font-size:12px;margin-top:2px}.setup-card{border:1px solid rgba(130,170,255,.18);background:linear-gradient(180deg,rgba(13,29,64,.96),rgba(7,14,34,.96));border-radius:28px;color:#fff;box-shadow:0 18px 60px rgba(0,0,0,.22)}.setup-hero{background:radial-gradient(circle at 20% 10%,rgba(57,130,255,.30),transparent 35%),radial-gradient(circle at 90% 20%,rgba(91,226,255,.16),transparent 26%),linear-gradient(135deg,rgba(7,19,46,.98),rgba(7,13,31,.96));border:1px solid rgba(130,170,255,.18);border-radius:28px;color:#fff}.text-muted-blue{color:#a7b8dc!important}.badge-soft{background:rgba(89,166,255,.14);color:#bfe4ff;border:1px solid rgba(89,166,255,.22)}.btn-magic{background:linear-gradient(135deg,#2188ff,#65e2ff);border:0;color:#03152c;font-weight:800}.btn-outline-magic{border:1px solid rgba(130,170,255,.28);color:#d7e8ff;background:rgba(255,255,255,.03)}.btn-outline-magic:hover{color:#fff;border-color:rgba(105,227,255,.7)}.app-card{display:flex;gap:14px;align-items:flex-start;border:1px solid rgba(130,170,255,.16);background:rgba(255,255,255,.035);border-radius:22px;padding:18px;height:100%;text-decoration:none;color:#fff}.app-card:hover{border-color:rgba(105,227,255,.65);color:#fff}.app-card .icon{width:42px;height:42px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:rgba(88,166,255,.13);font-size:22px;flex:0 0 auto}.copy-box{background:rgba(2,8,22,.72);border:1px solid rgba(130,170,255,.18);border-radius:16px;color:#dceaff;padding:14px;word-break:break-all;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:13px}.vpn-profile-card{border:1px solid rgba(130,170,255,.16);background:rgba(255,255,255,.035);border-radius:22px;padding:18px}.qr-box{width:132px;height:132px;background:#fff;border-radius:16px;padding:8px}.install-step{display:flex;gap:14px;margin-bottom:14px}.install-step .num{width:34px;height:34px;border-radius:12px;background:rgba(105,227,255,.14);color:#bff5ff;font-weight:900;display:flex;align-items:center;justify-content:center;flex:0 0 auto}.auto-panel{border:1px solid rgba(105,227,255,.28);background:linear-gradient(135deg,rgba(29,116,255,.18),rgba(53,226,255,.10));border-radius:24px;padding:22px}.small-note{border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.035);border-radius:18px;padding:14px;color:#a7b8dc}
  @media(max-width:991px){.wizard-steps{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(max-width:575px){.wizard-steps{grid-template-columns:1fr}.qr-box{width:110px;height:110px}}
</style>

<section class="setup-shell">
  <div class="wizard-steps">
    <div class="wizard-step done"><div class="n">✓</div><b>1. Выбор</b><span>Тариф выбран</span></div>
    <div class="wizard-step done"><div class="n">✓</div><b>2. Оплата</b><span>Подписка активна</span></div>
    <div class="wizard-step active"><div class="n">3</div><b>3. V2RayTun</b><span>Установите приложение</span></div>
    <div class="wizard-step active"><div class="n">4</div><b>4. Ключ</b><span>Импортируйте профиль</span></div>
  </div>

  <section class="setup-hero p-4 p-lg-5 mb-4">
    <div class="row g-4 align-items-center">
      <div class="col-lg-8">
        <span class="badge badge-soft rounded-pill mb-3">Шаги 3–4 из 4</span>
        <h1 class="display-5 fw-black mb-3">Установите V2RayTun и импортируйте ключ</h1>
        <p class="lead text-muted-blue mb-0">Подписка активна<?= $expiresTs ? ', действует до ' . e(date('d.m.Y H:i', $expiresTs)) . ' UTC' : '' ?>. Сначала установите приложение, затем нажмите автоимпорт.</p>
      </div>
      <div class="col-lg-4">
        <div class="auto-panel text-center">
          <div class="h5 fw-bold mb-2">Быстрый импорт</div>
          <p class="text-muted-blue small mb-3">Работает, если V2RayTun уже установлен на устройстве.</p>
          <a class="btn btn-magic btn-lg w-100" href="<?= e($autoImportUrl) ?>">Открыть в V2RayTun</a>
        </div>
      </div>
    </div>
  </section>

  <div class="row g-4 mb-4">
    <div class="col-lg-4">
      <a class="app-card" href="<?= e($androidUrl) ?>" target="_blank" rel="noopener">
        <div class="icon">🤖</div><div><b>Android</b><div class="text-muted-blue small mt-1">Скачать V2RayTun из Google Play.</div></div>
      </a>
    </div>
    <div class="col-lg-4">
      <a class="app-card" href="<?= e($iosUrl) ?>" target="_blank" rel="noopener">
        <div class="icon"></div><div><b>iPhone / iPad</b><div class="text-muted-blue small mt-1">Открыть страницу V2RayTun в App Store.</div></div>
      </a>
    </div>
    <div class="col-lg-4">
      <a class="app-card" href="<?= e($officialUrl) ?>" target="_blank" rel="noopener">
        <div class="icon">🌐</div><div><b>Официальный сайт</b><div class="text-muted-blue small mt-1">Сайт V2RayTun с версиями и инструкциями.</div></div>
      </a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="setup-card p-4 h-100">
        <span class="badge badge-soft rounded-pill mb-2">Шаг 3</span>
        <h2 class="h3 fw-bold mb-3">Как установить</h2>
        <div class="install-step"><div class="num">1</div><div><b>Скачайте V2RayTun</b><div class="text-muted-blue small">Используйте кнопки выше для вашей платформы.</div></div></div>
        <div class="install-step"><div class="num">2</div><div><b>Откройте приложение</b><div class="text-muted-blue small">Разрешите создание VPN-профиля, если система попросит.</div></div></div>
        <div class="install-step"><div class="num">3</div><div><b>Вернитесь на эту страницу</b><div class="text-muted-blue small">Нажмите “Открыть в V2RayTun” или импортируйте ссылку вручную.</div></div></div>
        <div class="small-note mt-3">Рекомендация: в настройках клиента включите <b class="text-white">Mux</b>. Остальные настройки лучше не менять.</div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="setup-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
          <div>
            <span class="badge badge-soft rounded-pill mb-2">Шаг 4</span>
            <h2 class="h3 fw-bold mb-1">Subscription-ссылка</h2>
            <div class="text-muted-blue">Лучший вариант: приложение само получит все активные серверы.</div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-magic" href="<?= e($autoImportUrl) ?>">Автоимпорт</a>
            <button type="button" class="btn btn-outline-magic" onclick="copyText('subUrl')">Скопировать</button>
          </div>
        </div>
        <div id="subUrl" class="copy-box"><?= e($subUrl) ?></div>
        <div class="small-note mt-3">Если автоимпорт не сработал: скопируйте ссылку, откройте V2RayTun → “+” → Import from Clipboard / Import subscription.</div>
      </div>

      <?php foreach($links as $i => $l): ?>
        <div class="vpn-profile-card mb-3">
          <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
              <div class="text-muted-blue small">Резервный VLESS-профиль #<?= (int)$i + 1 ?></div>
              <h5 class="mb-0"><?= e($l['remark']) ?></h5>
            </div>
            <button type="button" class="btn btn-outline-magic btn-sm" onclick="copyText('vless<?= (int)$i ?>')">Скопировать VLESS</button>
          </div>
          <div id="vless<?= (int)$i ?>" class="copy-box"><?= e($l['link']) ?></div>
          <div class="d-flex align-items-center gap-3 flex-wrap mt-3">
            <canvas class="qr-box" data-qr="<?= e($l['link']) ?>"></canvas>
            <div class="text-muted-blue small">Можно импортировать резервный профиль через QR-код, если subscription-ссылка не подходит.</div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
  function magicCopyFallback(id){
    var el = document.getElementById(id); if(!el) return;
    var text = el.innerText || el.textContent || '';
    if(navigator.clipboard){navigator.clipboard.writeText(text);}
  }
  if (typeof copyText !== 'function') { window.copyText = magicCopyFallback; }
</script>
