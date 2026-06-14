<div class="row g-4">
  <div class="col-lg-5">
    <div class="magic-card p-4">
      <span class="badge badge-soft rounded-pill mb-2">Поддержка</span><h1 class="fw-bold">Создать обращение</h1>
      <form method="post" action="/support/create" class="mt-3">
        <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
        <label class="form-label">Тема</label><input name="subject" class="form-control magic-input mb-3" required>
        <label class="form-label">Сообщение</label><textarea name="body" class="form-control magic-input" rows="6" required></textarea>
        <button class="btn btn-magic w-100 mt-3">Отправить</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="magic-card p-4">
      <h2 class="fw-bold mb-3">Мои обращения</h2>
      <?php if(!$tickets): ?><div class="text-muted-blue">Обращений пока нет.</div><?php endif; ?>
      <div class="vstack gap-2">
      <?php foreach($tickets as $t): ?>
        <a class="feature-card text-decoration-none" href="/support/view?id=<?=e((string)$t['id'])?>"><div class="d-flex justify-content-between gap-3"><b><?=e($t['subject'])?></b><span><?=e(ticket_status_label($t['status']))?></span></div><div class="text-muted-blue small"><?=e($t['last_message_at'])?></div></a>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
