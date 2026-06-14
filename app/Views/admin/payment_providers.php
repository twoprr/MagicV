<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Продажи</span>
      <h2 class="fw-bold mb-0">Способы оплаты</h2>
      <div class="text-muted-blue mt-2">Включите нужный способ и заполните JSON-конфиг.</div>
    </div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>

  <?php foreach ($providers as $p): ?>
    <form method="post" class="feature-card mb-3">
      <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
      <input type="hidden" name="code" value="<?= e($p['code']) ?>">
      <div class="row g-3 align-items-start">
        <div class="col-md-2">
          <label class="form-label">Код</label>
          <input class="form-control" value="<?= e($p['code']) ?>" disabled>
        </div>
        <div class="col-md-4">
          <label class="form-label">Название</label>
          <input class="form-control" name="title" value="<?= e($p['title']) ?>" required>
        </div>
        <div class="col-md-3 pt-md-4">
          <div class="form-check mt-md-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="provider_<?=e($p['code'])?>" <?= !empty($p['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="provider_<?=e($p['code'])?>">Активен</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Config JSON</label>
          <textarea class="form-control font-monospace" name="config_json" rows="6"><?= e($p['config_json'] ?? '{}') ?></textarea>
          <div class="small text-muted-blue mt-2">
            manual можно оставить как {}. Для CryptoCloud/AAIO укажите ключи провайдера.
          </div>
        </div>
        <div class="col-12">
          <button class="btn btn-magic">Сохранить <?= e($p['code']) ?></button>
        </div>
      </div>
    </form>
  <?php endforeach; ?>

  <?php if(empty($providers)): ?>
    <div class="alert alert-warning">Провайдеры не найдены. Запустите: <code>php scripts/fix_payments_plans_schema.php</code> и затем <code>php scripts/seed_default_plans.php</code>.</div>
  <?php endif; ?>
</div>
