<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><span class="badge badge-soft rounded-pill mb-2">Админка</span><h1 class="fw-bold mb-0">Очередь задач</h1></div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>
  <div class="row g-3 mb-4">
    <?php foreach(['pending'=>'В очереди','running'=>'В работе','done'=>'Готово','failed'=>'Ошибки'] as $k=>$label): ?>
      <div class="col-md-3"><div class="feature-card"><div class="text-muted-blue"><?=e($label)?></div><h3><?=e((string)($jobStats[$k] ?? 0))?></h3></div></div>
    <?php endforeach; ?>
  </div>
  <div class="d-flex flex-wrap gap-2 mb-4">
    <form method="post" action="/admin/jobs/run"><input type="hidden" name="_csrf" value="<?=csrf_token()?>"><button class="btn btn-magic">Обработать 5 задач</button></form>
    <form method="post" action="/admin/jobs/sync-all"><input type="hidden" name="_csrf" value="<?=csrf_token()?>"><button class="btn btn-outline-magic" onclick="return confirm('Поставить в очередь синхронизацию всех активных пользователей?')">Синхронизировать всех активных</button></form>
  </div>
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
      <thead><tr><th>ID</th><th>Тип</th><th>Статус</th><th>Попытки</th><th>Доступна</th><th>Ошибка</th><th></th></tr></thead>
      <tbody>
      <?php foreach($jobs as $j): ?>
        <tr>
          <td><?=e((string)$j['id'])?></td>
          <td><code><?=e($j['type'])?></code></td>
          <td><?=e($j['status'])?></td>
          <td><?=e((string)$j['attempts'])?> / <?=e((string)$j['max_attempts'])?></td>
          <td><?=e((string)$j['available_at'])?></td>
          <td style="max-width:420px"><small><?=e(mb_strimwidth((string)($j['last_error'] ?? ''),0,300,'…'))?></small></td>
          <td><?php if($j['status']==='failed'): ?><form method="post" action="/admin/jobs/retry"><input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=e((string)$j['id'])?>"><button class="btn btn-sm btn-outline-magic">Повторить</button></form><?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
