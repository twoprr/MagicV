<div class="row justify-content-center align-items-center min-vh-50">
  <div class="col-lg-5">
    <div class="magic-card p-4 p-lg-5">
      <span class="badge badge-soft rounded-pill mb-3">Новый пароль</span>
      <h1 class="fw-bold mb-2">Задайте новый пароль</h1>
      <p class="text-muted-blue mb-4">Аккаунт: <strong><?=e($resetUser['email'] ?? '')?></strong>. Пароль должен быть не короче 8 символов.</p>

      <form method="post" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
        <input type="hidden" name="token" value="<?=e($token ?? '')?>">
        <div>
          <label class="form-label">Новый пароль</label>
          <input class="form-control" type="password" name="password" minlength="8" required autofocus>
        </div>
        <div>
          <label class="form-label">Повторите пароль</label>
          <input class="form-control" type="password" name="password_confirm" minlength="8" required>
        </div>
        <button class="btn btn-magic w-100">Сохранить пароль</button>
      </form>

      <div class="text-muted-blue mt-4 small">Ссылка действует 60 минут. После смены пароля старую ссылку использовать нельзя.</div>
    </div>
  </div>
</div>
