<div class="row justify-content-center align-items-center min-vh-50">
  <div class="col-lg-5">
    <div class="magic-card p-4 p-lg-5">
      <span class="badge badge-soft rounded-pill mb-3">Личный кабинет</span>
      <h1 class="fw-bold mb-2">Вход в MagicVPN</h1>
      <p class="text-muted-blue mb-4">Введите email и пароль, чтобы открыть подписку и VPN‑ключи.</p>
      <form method="post" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
        <div><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?=e($_GET['email'] ?? '')?>" placeholder="you@example.com" required></div>
        <div>
          <div class="d-flex justify-content-between align-items-center gap-3">
            <label class="form-label mb-0">Пароль</label>
            <a class="small" href="/forgot-password">Забыли пароль?</a>
          </div>
          <input class="form-control mt-2" type="password" name="password" required>
        </div>
        <button class="btn btn-magic w-100">Войти</button>
      </form>

      <?php if (!empty($_GET['verify'])): ?>
        <hr class="border-secondary my-4">
        <form method="post" action="/resend-verification" class="vstack gap-2">
          <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
          <input type="hidden" name="email" value="<?=e($_GET['email'] ?? '')?>">
          <button class="btn btn-outline-magic w-100">Отправить письмо подтверждения ещё раз</button>
        </form>
      <?php endif; ?>

      <div class="text-muted-blue mt-4">Нет аккаунта? <a href="/register">Создать аккаунт</a></div>
    </div>
  </div>
</div>
