<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Пополнения</span>
      <h1 class="fw-bold mb-0">Заявки на пополнение баланса</h1>
      <div class="text-muted-blue mt-2">Подтвердите заявку, чтобы сумма зачислилась на баланс пользователя.</div>
    </div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>

  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
      <thead><tr><th>ID</th><th>Пользователь</th><th>Сумма</th><th>Статус</th><th>Чек</th><th>Дата</th><th>Действия</th></tr></thead>
      <tbody>
      <?php foreach($topups as $t): ?>
        <tr>
          <td>#<?=e((string)$t['id'])?></td>
          <td><?=e($t['email'])?><div class="text-muted-blue small"><?=e((string)$t['name'])?></div></td>
          <td><b><?=e((string)$t['amount'])?> ₽</b></td>
          <td><span class="badge badge-soft"><?=e($t['status'])?></span></td>
          <td><?php if(!empty($t['receipt_path'])): ?><a href="/<?=e($t['receipt_path'])?>" target="_blank">Открыть</a><?php else: ?><span class="text-muted-blue">нет</span><?php endif; ?></td>
          <td class="text-muted-blue small"><?=e($t['created_at'])?></td>
          <td>
            <?php if($t['status'] === 'pending'): ?>
              <div class="d-flex gap-2 flex-wrap">
                <form method="post" action="/admin/balance-topups/approve">
                  <input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=(int)$t['id']?>">
                  <button class="btn btn-magic btn-sm">Подтвердить</button>
                </form>
                <form method="post" action="/admin/balance-topups/reject" class="d-flex gap-1">
                  <input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=(int)$t['id']?>">
                  <input class="form-control form-control-sm" name="comment" placeholder="Причина">
                  <button class="btn btn-outline-magic btn-sm">Отклонить</button>
                </form>
              </div>
            <?php else: ?>
              <span class="text-muted-blue small"><?=e((string)$t['admin_comment'])?></span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
