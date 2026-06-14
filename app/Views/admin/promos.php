<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><span class="badge badge-soft rounded-pill mb-2">Админка</span><h2 class="fw-bold mb-0">Промокоды</h2></div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>
  <form method="post" action="/admin/promos/create" class="feature-card mb-4">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
    <div class="row g-3 align-items-end">
      <div class="col-md-3"><label class="form-label">Код</label><input class="form-control" name="code" placeholder="WELCOME10" required></div>
      <div class="col-md-2"><label class="form-label">Тип</label><select class="form-select" name="type"><option value="percent">% скидка</option><option value="fixed">₽ скидка</option><option value="days">+ дни</option></select></div>
      <div class="col-md-2"><label class="form-label">Значение</label><input class="form-control" type="number" min="1" name="value" value="10" required></div>
      <div class="col-md-2"><label class="form-label">Лимит</label><input class="form-control" type="number" min="1" name="max_uses" placeholder="∞"></div>
      <div class="col-md-2"><label class="form-label">Истекает</label><input class="form-control" type="datetime-local" name="expires_at"></div>
      <div class="col-md-1"><button class="btn btn-magic w-100">+</button></div>
    </div>
  </form>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Код</th><th>Тип</th><th>Значение</th><th>Использовано</th><th>Истекает</th><th>Статус</th><th></th></tr></thead><tbody>
  <?php foreach($promos as $p): ?>
    <tr>
      <td><b><?=e($p['code'])?></b></td>
      <td><?=e($p['type'])?></td>
      <td><?=e((string)$p['value'])?></td>
      <td><?=e((string)$p['used_count'])?><?= $p['max_uses'] ? ' / '.e((string)$p['max_uses']) : '' ?></td>
      <td><?= $p['expires_at'] ? e(date('d.m.Y H:i', strtotime($p['expires_at']))) : '—' ?></td>
      <td><span class="badge badge-soft"><?= $p['active'] ? 'активен' : 'выключен' ?></span></td>
      <td class="text-end"><form method="post" action="/admin/promos/toggle"><input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-outline-magic btn-sm"><?= $p['active'] ? 'Выключить' : 'Включить' ?></button></form></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
</div>
