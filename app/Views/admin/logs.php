<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h2 class="mb-1">Логи админки</h2>
      <p class="text-muted-blue mb-0">Последние действия: подтверждения, отклонения, продления.</p>
    </div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Дата</th><th>Админ</th><th>Действие</th><th>Пользователь</th><th>Детали</th></tr></thead>
      <tbody>
        <?php foreach($logs as $l): ?>
          <tr>
            <td><?=e(date('d.m.Y H:i', strtotime($l['created_at'])))?></td>
            <td><?=e((string)($l['admin_email'] ?? '—'))?></td>
            <td><span class="badge badge-soft rounded-pill"><?=e((string)$l['action'])?></span></td>
            <td><?=e((string)($l['target_email'] ?? ($l['target_user_id'] ?? '—')))?></td>
            <td class="small text-muted-blue" style="max-width:520px;white-space:normal;"><?=e((string)($l['details'] ?? ''))?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
