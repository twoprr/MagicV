<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><span class="badge badge-soft rounded-pill mb-2">Админка</span><h1 class="fw-bold mb-0">Тикеты поддержки</h1></div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
      <thead><tr><th>ID</th><th>Пользователь</th><th>Тема</th><th>Статус</th><th>Последнее сообщение</th><th></th></tr></thead>
      <tbody>
      <?php foreach($tickets as $t): ?>
      <tr>
        <td><?=e((string)$t['id'])?></td><td><?=e($t['email'])?></td><td><?=e($t['subject'])?></td><td><?=e(ticket_status_label($t['status']))?></td><td><?=e($t['last_message_at'])?></td>
        <td><a class="btn btn-sm btn-outline-magic" href="/admin/tickets/view?id=<?=e((string)$t['id'])?>">Открыть</a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
