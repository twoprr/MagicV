<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Админ-панель</span>
      <h1 class="fw-bold mb-0">Управление MagicVPN</h1>
      <div class="text-muted-blue mt-2">Тарифы, платежи, пользователи, Xray, уведомления и поддержка.</div>
    </div>
    <a class="btn btn-outline-magic" href="/dashboard">Открыть кабинет</a>
  </div>

  <?php if(!empty($stats)): ?>
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="feature-card"><div class="text-muted-blue">Активных подписок</div><h3><?=e((string)($stats['active_subs'] ?? 0))?></h3></div></div>
    <div class="col-md-3"><div class="feature-card"><div class="text-muted-blue">Выручка за месяц</div><h3><?=e((string)($stats['revenue_month'] ?? 0))?> ₽</h3></div></div>
    <div class="col-md-3"><div class="feature-card"><div class="text-muted-blue">Истекают за 3 дня</div><h3><?=e((string)($stats['expiring_3d'] ?? 0))?></h3></div></div>
    <div class="col-md-3"><div class="feature-card"><div class="text-muted-blue">Открытые тикеты</div><h3><?=e((string)($stats['open_tickets'] ?? 0))?></h3></div></div>
  </div>
  <?php endif; ?>

  <h5 class="fw-bold mb-3">Продажи</h5>
  <div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/plans"><h2>₽</h2><span class="text-muted-blue">Тарифы</span><div class="small text-muted-blue mt-2">Цена, срок, бейджи, активность</div></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/payment-providers"><h2>PAY</h2><span class="text-muted-blue">Способы оплаты</span><div class="small text-muted-blue mt-2">Manual, CryptoCloud, AAIO</div></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/payments"><h2>LOG</h2><span class="text-muted-blue">Платежи</span><div class="small text-muted-blue mt-2">История и статусы платежей</div></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/balance-topups"><h2>TOP</h2><span class="text-muted-blue">Пополнения баланса</span><div class="small text-muted-blue mt-2">Проверка чеков и зачисления</div></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/balances"><h2>₽</h2><span class="text-muted-blue">Балансы</span><div class="small text-muted-blue mt-2">Ручное начисление и списание</div></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/orders"><h2><?=e((string)$pending)?></h2><span class="text-muted-blue">Заявки на оплату</span><div class="small text-muted-blue mt-2">Ручные чеки и подтверждения</div></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/promos"><h2>%</h2><span class="text-muted-blue">Промокоды</span><div class="small text-muted-blue mt-2">Скидки и бонусные дни</div></a></div>
  </div>

  <h5 class="fw-bold mb-3">Пользователи и VPN</h5>
  <div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/users"><h2><?=e((string)$users)?></h2><span class="text-muted-blue">Пользователи</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/extend"><h2>+</h2><span class="text-muted-blue">Продлить подписки</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/status"><h2>●</h2><span class="text-muted-blue">Статус серверов</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/jobs"><h2>JOB</h2><span class="text-muted-blue">Очередь задач</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/import"><h2>⇄</h2><span class="text-muted-blue">Импорт из бота</span></a></div>
  </div>

  <h5 class="fw-bold mb-3">Система</h5>
  <div class="row g-3">
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/email-settings"><h2>SMTP</h2><span class="text-muted-blue">SMTP-настройки</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/security-settings"><h2>SEC</h2><span class="text-muted-blue">Регистрация и капча</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/notices"><h2>!</h2><span class="text-muted-blue">Уведомления</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/emails"><h2>@</h2><span class="text-muted-blue">Email-логи</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/tickets"><h2>?</h2><span class="text-muted-blue">Тикеты поддержки</span></a></div>
    <div class="col-md-4 col-xl-3"><a class="feature-card d-block text-decoration-none" href="/admin/logs"><h2>☰</h2><span class="text-muted-blue">Логи админки</span></a></div>
  </div>
</div>
