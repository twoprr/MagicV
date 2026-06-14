<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Безопасность</span>
      <h1 class="fw-bold mb-0">Регистрация и Google reCAPTCHA</h1>
      <p class="text-muted-blue mb-0">Email-подтверждение и reCAPTCHA по умолчанию выключены.</p>
    </div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>

  <form method="post" action="/admin/security-settings/save" class="vstack gap-4">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>">

    <div class="p-3 rounded-4 border border-secondary border-opacity-25">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="emailVerify" name="registration_email_verify_enabled" value="1" <?=($settings['registration_email_verify_enabled'] ?? '0') === '1' ? 'checked' : ''?>>
        <label class="form-check-label fw-semibold" for="emailVerify">Подтверждение регистрации через Email</label>
      </div>
      <div class="text-muted-blue small mt-2">Если включено, новый пользователь не сможет войти, пока не перейдёт по ссылке из письма. Для работы нужен настроенный SMTP.</div>
    </div>

    <div class="p-3 rounded-4 border border-secondary border-opacity-25">
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="captcha" name="registration_captcha_enabled" value="1" <?=($settings['registration_captcha_enabled'] ?? '0') === '1' ? 'checked' : ''?>>
        <label class="form-check-label fw-semibold" for="captcha">Google reCAPTCHA при регистрации</label>
      </div>

      <input type="hidden" name="registration_captcha_provider" value="google">

      <div class="row g-3">
        <div class="col-lg-6">
          <label class="form-label">Site Key</label>
          <input class="form-control" name="recaptcha_site_key" value="<?=e($settings['recaptcha_site_key'] ?? '')?>" placeholder="6Lc...">
        </div>
        <div class="col-lg-6">
          <label class="form-label">Secret Key</label>
          <input class="form-control" name="recaptcha_secret_key" value="<?=e($settings['recaptcha_secret_key'] ?? '')?>" placeholder="6Lc...">
        </div>
        <div class="col-12">
          <label class="form-label">Подпись блока капчи</label>
          <input class="form-control" name="registration_captcha_label" value="<?=e($settings['registration_captcha_label'] ?? 'Подтвердите, что вы не робот')?>">
        </div>
      </div>

      <div class="text-muted-blue small mt-3">
        Используется Google reCAPTCHA v2 Checkbox. Ключи создаются в Google reCAPTCHA Admin Console для домена сайта.
        Пока ключи не заданы, не включайте капчу, иначе пользователи не смогут зарегистрироваться.
      </div>
    </div>

    <button class="btn btn-magic">Сохранить настройки</button>
  </form>
</div>
