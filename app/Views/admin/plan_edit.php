<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Тариф</span>
      <h2 class="fw-bold mb-0"><?= $plan ? 'Редактировать тариф' : 'Новый тариф' ?></h2>
    </div>
    <a class="btn btn-outline-magic" href="/admin/plans">Назад</a>
  </div>

  <form method="post" class="row g-3">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
    <div class="col-md-6">
      <label class="form-label">Название</label>
      <input class="form-control" name="title" value="<?= e($plan['title'] ?? '') ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Дней</label>
      <input class="form-control" type="number" name="days" value="<?= e((string)($plan['days'] ?? 30)) ?>" min="1" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Цена ₽</label>
      <input class="form-control" type="number" name="price" value="<?= e((string)($plan['price'] ?? 499)) ?>" min="0" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Старая цена</label>
      <input class="form-control" type="number" name="old_price" value="<?= e((string)($plan['old_price'] ?? '')) ?>" min="0">
    </div>
    <div class="col-md-3">
      <label class="form-label">Бейдж</label>
      <input class="form-control" name="badge" value="<?= e($plan['badge'] ?? '') ?>" placeholder="Например: Хит">
    </div>
    <div class="col-md-3">
      <label class="form-label">Сортировка</label>
      <input class="form-control" type="number" name="sort_order" value="<?= e((string)($plan['sort_order'] ?? 100)) ?>">
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <div class="form-check me-4">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= !empty($plan['is_active']) || !$plan ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_active">Активен</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_popular" value="1" id="is_popular" <?= !empty($plan['is_popular']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_popular">Популярный</label>
      </div>
    </div>
    <div class="col-12">
      <label class="form-label">Описание</label>
      <textarea class="form-control" name="description" rows="3" placeholder="Короткое описание тарифа"><?= e($plan['description'] ?? '') ?></textarea>
    </div>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-magic">Сохранить</button>
      <a href="/admin/plans" class="btn btn-outline-magic">Отмена</a>
    </div>
  </form>
</div>
