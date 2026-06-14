<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Email</span>
      <h1 class="fw-bold mb-0">SMTP-настройки</h1>
      <div class="text-muted-blue mt-2">Настройте почту для уведомлений об оплате, окончании подписки и системных сообщений.</div>
    </div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>

  <form method="post" action="/admin/email-settings/save" class="row g-3">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
    <div class="col-md-3">
      <label class="form-label">Включить SMTP</label>
      <select name="smtp_enabled" class="form-select">
        <option value="0" <?=($settings['smtp_enabled'] ?? '0')==='0'?'selected':''?>>Нет</option>
        <option value="1" <?=($settings['smtp_enabled'] ?? '0')==='1'?'selected':''?>>Да</option>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">SMTP host</label>
      <input name="smtp_host" class="form-control" value="<?=e($settings['smtp_host'] ?? '')?>" placeholder="smtp.yandex.ru">
    </div>
    <div class="col-md-3">
      <label class="form-label">Порт</label>
      <input name="smtp_port" class="form-control" value="<?=e($settings['smtp_port'] ?? '587')?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Шифрование</label>
      <select name="smtp_encryption" class="form-select">
        <?php foreach(['tls'=>'TLS / STARTTLS','ssl'=>'SSL','none'=>'Без шифрования'] as $k=>$v): ?>
          <option value="<?=$k?>" <?=($settings['smtp_encryption'] ?? 'tls')===$k?'selected':''?>><?=$v?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Логин</label>
      <input name="smtp_username" class="form-control" value="<?=e($settings['smtp_username'] ?? '')?>" autocomplete="off">
    </div>
    <div class="col-md-4">
      <label class="form-label">Пароль / app password</label>
      <input name="smtp_password" type="password" class="form-control" value="<?=e($settings['smtp_password'] ?? '')?>" autocomplete="new-password">
    </div>
    <div class="col-md-6">
      <label class="form-label">From email</label>
      <input name="smtp_from_email" class="form-control" value="<?=e($settings['smtp_from_email'] ?? '')?>" placeholder="no-reply@domain.ru">
    </div>
    <div class="col-md-4">
      <label class="form-label">From name</label>
      <input name="smtp_from_name" class="form-control" value="<?=e($settings['smtp_from_name'] ?? 'MagicVPN')?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Timeout</label>
      <input name="smtp_timeout" class="form-control" value="<?=e($settings['smtp_timeout'] ?? '20')?>">
    </div>
    <div class="col-12 d-flex gap-2 flex-wrap">
      <button class="btn btn-magic">Сохранить SMTP</button>
      <a class="btn btn-outline-magic" href="/admin/emails">Email-логи</a>
    </div>
  </form>

  <hr class="my-4 border-secondary">
  <form method="post" action="/admin/email-settings/test" class="row g-3">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
    <div class="col-md-8">
      <label class="form-label">Отправить тест на email</label>
      <input name="test_email" type="email" class="form-control" value="<?=e($user['email'] ?? '')?>">
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button class="btn btn-outline-magic w-100">Отправить тест</button>
    </div>
  </form>
</div>
