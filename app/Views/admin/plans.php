<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Продажи</span>
      <h2 class="fw-bold mb-0">Тарифы</h2>
      <div class="text-muted-blue mt-2">Эти тарифы показываются пользователю на странице покупки.</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-magic" href="/admin">Назад</a>
      <a class="btn btn-magic" href="/admin/plans/edit">Добавить тариф</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Название</th>
          <th>Дней</th>
          <th>Цена</th>
          <th>Старая цена</th>
          <th>Бейдж</th>
          <th>Сорт.</th>
          <th>Статус</th>
          <th class="text-end">Действие</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($plans as $p): ?>
        <tr>
          <td><?=e((string)$p['id'])?></td>
          <td>
            <strong><?=e($p['title'])?></strong>
            <?php if(!empty($p['is_popular'])): ?><span class="badge bg-info ms-2">популярный</span><?php endif; ?>
            <?php if(!empty($p['description'])): ?><div class="small text-muted-blue mt-1"><?=e($p['description'])?></div><?php endif; ?>
          </td>
          <td><?=e((string)$p['days'])?></td>
          <td><?=e((string)$p['price'])?> ₽</td>
          <td><?=!empty($p['old_price']) ? e((string)$p['old_price']).' ₽' : '—'?></td>
          <td><?=!empty($p['badge']) ? e($p['badge']) : '—'?></td>
          <td><?=e((string)($p['sort_order'] ?? 100))?></td>
          <td><?=!empty($p['is_active']) ? '<span class="badge bg-success">активен</span>' : '<span class="badge bg-secondary">скрыт</span>'?></td>
          <td class="text-end"><a class="btn btn-sm btn-outline-magic" href="/admin/plans/edit?id=<?=e((string)$p['id'])?>">Редактировать</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($plans)): ?>
        <tr><td colspan="9" class="text-center text-muted-blue py-4">Тарифов пока нет. Нажмите “Добавить тариф”.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
