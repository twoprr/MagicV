<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div><span class="badge badge-soft rounded-pill mb-2">Админка</span><h2 class="fw-bold mb-0">Email-логи</h2></div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Дата</th><th>Email</th><th>Тема</th><th>Статус</th><th>Ошибка</th></tr></thead><tbody>
  <?php foreach($emails as $m): ?>
    <tr><td><?=e(date('d.m.Y H:i', strtotime($m['created_at'])))?></td><td><?=e($m['email'])?></td><td><?=e($m['subject'])?></td><td><span class="badge badge-soft"><?=e($m['status'])?></span></td><td><?=e((string)($m['error_text'] ?? ''))?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</div>
