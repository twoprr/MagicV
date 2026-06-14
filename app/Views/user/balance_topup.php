<?php $payment = $payment ?? []; ?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="magic-card p-4 p-lg-5">
      <span class="badge badge-soft rounded-pill mb-3">Пополнение #<?=e((string)$topup['id'])?></span>
      <h1 class="fw-bold mb-2">Загрузите чек</h1>
      <p class="text-muted-blue">Сумма к оплате: <b class="text-white"><?=e((string)$topup['amount'])?> ₽</b></p>

      <?php if(!empty($payment)): ?>
      <div class="compact-panel mb-4">
        <h5 class="mb-2">Реквизиты оплаты</h5>
        <div class="text-muted-blue small"><?=nl2br(e(is_array($payment) ? json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string)$payment))?></div>
      </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
        <div>
          <label class="form-label">Чек оплаты</label>
          <input class="form-control" type="file" name="receipt" accept="image/*,.pdf" required>
        </div>
        <button class="btn btn-magic">Отправить чек на проверку</button>
        <a class="btn btn-outline-magic" href="/balance">Назад к балансу</a>
      </form>
    </div>
  </div>
</div>
