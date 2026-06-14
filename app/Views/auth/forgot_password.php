<div class="row justify-content-center align-items-center min-vh-50">
  <div class="col-lg-5">
    <div class="magic-card p-4 p-lg-5">
      <span class="badge badge-soft rounded-pill mb-3">Восстановление доступа</span>
      <h1 class="fw-bold mb-2">Забыли пароль?</h1>
      <p class="text-muted-blue mb-4">Введите Email аккаунта. Если он зарегистрирован, мы отправим ссылку для смены пароля.</p>

      <form method="post" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
        <div>
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" placeholder="you@example.com" required autofocus>
        </div>
        <button class="btn btn-magic w-100">Отправить ссылку</button>
      </form>

      <div class="d-flex justify-content-between gap-3 mt-4 small">
        <a href="/login">Вернуться ко входу</a>
        <a href="/register">Создать аккаунт</a>
      </div>
    </div>
  </div>
</div>
