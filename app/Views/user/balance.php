<?php
$balance = (int)($balance ?? 0);
$transactions = $transactions ?? [];
$topups = $topups ?? [];
$payment = $payment ?? [];
?>
<div class="magic-card p-4 p-lg-5 mb-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Баланс</span>
      <h1 class="fw-bold mb-1">Ваш баланс</h1>
      <div class="text-muted-blue">Пополняйте баланс один раз и оплачивайте тарифы в один клик.</div>
    </div>
    <div class="text-end">
      <div class="text-muted-blue small">Доступно</div>
      <div class="display-6 fw-black text-white"><?=e((string)$balance)?> ₽</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="magic-card p-4 h-100">
      <h2 class="h4 fw-bold mb-3">Пополнить баланс</h2>
      <form method="post" action="/balance/topup" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
        <div>
          <label class="form-label">Сумма пополнения, ₽</label>
          <input class="form-control" type="number" min="50" max="100000" step="1" name="amount" placeholder="500" required>
          <div class="text-muted-blue small mt-2">Минимум 50 ₽. После создания заявки загрузите чек.</div>
        </div>
        <button class="btn btn-magic">Создать заявку на пополнение</button>
      </form>

      <?php if(!empty($payment)): ?>
      <div class="compact-panel mt-4">
        <h5 class="mb-2">Реквизиты для ручной оплаты</h5>
        <div class="text-muted-blue small"><?=nl2br(e(is_array($payment) ? json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string)$payment))?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="magic-card p-4 mb-4">
      <h2 class="h4 fw-bold mb-3">Заявки на пополнение</h2>
      <?php if(empty($topups)): ?>
        <div class="text-muted-blue">Заявок пока нет.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-dark table-hover align-middle mb-0">
            <thead><tr><th>ID</th><th>Сумма</th><th>Статус</th><th>Дата</th><th></th></tr></thead>
            <tbody>
            <?php foreach($topups as $t): ?>
              <tr>
                <td>#<?=e((string)$t['id'])?></td>
                <td><?=e((string)$t['amount'])?> ₽</td>
                <td><span class="badge badge-soft"><?=e($t['status'])?></span></td>
                <td class="text-muted-blue small"><?=e($t['created_at'])?></td>
                <td><?php if($t['status'] !== 'approved'): ?><a class="btn btn-outline-magic btn-sm" href="/balance/topup/view?id=<?=(int)$t['id']?>">Открыть</a><?php endif; ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="magic-card p-4">
      <h2 class="h4 fw-bold mb-3">История баланса</h2>
      <?php if(empty($transactions)): ?>
        <div class="text-muted-blue">Операций пока нет.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-dark table-hover align-middle mb-0">
            <thead><tr><th>Дата</th><th>Операция</th><th>Комментарий</th><th class="text-end">Сумма</th></tr></thead>
            <tbody>
            <?php foreach($transactions as $tr): ?>
              <?php $amount=(int)$tr['amount']; ?>
              <tr>
                <td class="text-muted-blue small"><?=e($tr['created_at'])?></td>
                <td><?=e($tr['type'])?></td>
                <td class="text-muted-blue small"><?=e((string)$tr['comment'])?></td>
                <td class="text-end <?= $amount >= 0 ? 'text-success' : 'text-danger' ?> fw-bold"><?= $amount >= 0 ? '+' : '' ?><?=e((string)$amount)?> ₽</td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
