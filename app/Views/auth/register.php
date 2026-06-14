<div class="row justify-content-center align-items-center min-vh-50">
  <div class="col-lg-6">
    <div class="magic-card p-4 p-lg-5">
      <span class="badge badge-soft rounded-pill mb-3">Подключение</span>
      <h1 class="fw-bold mb-2">Создать аккаунт MagicVPN</h1>
      <p class="text-muted-blue mb-4">После регистрации вы сможете выбрать тариф, пополнить баланс и получить VLESS‑профили.</p>

      <?php if (!empty($emailVerifyEnabled)): ?>
        <div class="alert alert-info border-0">После регистрации нужно подтвердить Email по ссылке из письма.</div>
      <?php endif; ?>

      <form method="post" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
        <div><label class="form-label">Имя</label><input class="form-control" name="name" placeholder="Ваше имя"></div>
        <div><label class="form-label">Email</label><input class="form-control" type="email" name="email" placeholder="you@example.com" required></div>
        <div><label class="form-label">Пароль</label><input class="form-control" type="password" name="password" minlength="8" required></div>

        <?php if (!empty($captcha) && ($captcha['provider'] ?? '') === 'google'): ?>
          <div>
            <label class="form-label"><?=e($captcha['label'] ?? 'Подтвердите, что вы не робот')?></label>
            <?php if (!empty($captcha['site_key'])): ?>
              <div class="g-recaptcha" data-sitekey="<?=e($captcha['site_key'])?>"></div>
              <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <?php else: ?>
              <div class="alert alert-warning border-0">Google reCAPTCHA включена, но Site Key не задан. Регистрация будет недоступна до настройки ключей в админке.</div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <button class="btn btn-magic w-100">Создать аккаунт</button>
      </form>
      <div class="text-muted-blue mt-4">Уже есть аккаунт? <a href="/login">Войти</a></div>
    </div>
  </div>
</div>
