<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><span class="badge badge-soft rounded-pill mb-2">Поддержка</span><h1 class="fw-bold mb-0"><?=e($ticket['subject'])?></h1><div class="text-muted-blue"><?=e(ticket_status_label($ticket['status']))?></div></div>
    <a class="btn btn-outline-magic" href="/support">Назад</a>
  </div>
  <div class="vstack gap-3 mb-4">
    <?php foreach($messages as $m): ?>
      <div class="feature-card <?=!empty($m['is_admin'])?'border border-info':''?>">
        <div class="small text-muted-blue mb-2"><?=!empty($m['is_admin'])?'Поддержка MagicVPN':'Вы'?> · <?=e($m['created_at'])?></div>
        <div style="white-space:pre-line"><?=e($m['body'])?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if($ticket['status'] !== 'closed'): ?>
  <form method="post" action="/support/reply">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=e((string)$ticket['id'])?>">
    <textarea name="body" class="form-control magic-input" rows="4" required placeholder="Напишите сообщение..."></textarea>
    <button class="btn btn-magic mt-3">Отправить</button>
  </form>
  <?php else: ?><div class="alert alert-secondary">Тикет закрыт.</div><?php endif; ?>
</div>
