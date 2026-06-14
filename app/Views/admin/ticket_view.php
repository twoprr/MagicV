<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><span class="badge badge-soft rounded-pill mb-2">Тикет #<?=e((string)$ticket['id'])?></span><h1 class="fw-bold mb-0"><?=e($ticket['subject'])?></h1><div class="text-muted-blue"><?=e($ticket['email'])?> · <?=e(ticket_status_label($ticket['status']))?></div></div>
    <a class="btn btn-outline-magic" href="/admin/tickets">Назад</a>
  </div>
  <div class="vstack gap-3 mb-4">
    <?php foreach($messages as $m): ?>
      <div class="feature-card <?=!empty($m['is_admin'])?'border border-info':''?>">
        <div class="small text-muted-blue mb-2"><?=!empty($m['is_admin'])?'Администратор':'Пользователь'?> · <?=e($m['created_at'])?></div>
        <div style="white-space:pre-line"><?=e($m['body'])?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <form method="post" action="/admin/tickets/reply" class="mb-3">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=e((string)$ticket['id'])?>">
    <label class="form-label">Ответ</label><textarea name="body" class="form-control magic-input" rows="5" required></textarea>
    <button class="btn btn-magic mt-3">Отправить ответ</button>
  </form>
  <form method="post" action="/admin/tickets/close"><input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=e((string)$ticket['id'])?>"><button class="btn btn-outline-danger">Закрыть тикет</button></form>
</div>
