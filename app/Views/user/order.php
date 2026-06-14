<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="magic-card p-4 p-lg-5">
      <span class="badge badge-soft rounded-pill mb-3">Оплата</span>
      <h1 class="fw-bold">Заявка ORD<?=e((string)$order['id'])?></h1>
      <p class="text-muted-blue">Оплатите переводом и загрузите чек. После подтверждения доступ появится в личном кабинете.</p>
      <div class="feature-card mb-4">
        <div class="row g-3">
          <div class="col-sm-6"><span class="text-muted-blue">Сумма</span><h4><?=e((string)$order['amount'])?> ₽</h4><?php if(!empty($order['promo_code'])): ?><small class="text-muted-blue">Промокод: <?=e($order['promo_code'])?><?php if((int)($order['discount_amount'] ?? 0)>0): ?>, скидка <?=e((string)$order['discount_amount'])?> ₽<?php endif; ?></small><?php endif; ?></div>
          <div class="col-sm-6"><span class="text-muted-blue">Комментарий</span><h4>ORD<?=e((string)$order['id'])?></h4></div>
          <div class="col-sm-6"><span class="text-muted-blue">Телефон</span><div><?=e($payment['phone'])?></div></div>
          <div class="col-sm-6"><span class="text-muted-blue">Банк</span><div><?=e($payment['bank'])?></div></div>
          <div class="col-12"><span class="text-muted-blue">Получатель</span><div><?=e($payment['holder'])?></div></div>
        </div>
      </div>
      <form method="post" enctype="multipart/form-data" class="vstack gap-3">
        <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
        <div><label class="form-label">Чек оплаты</label><input class="form-control" type="file" name="receipt" accept="image/*,.pdf" required></div>
        <button class="btn btn-magic">Отправить заявку</button>
      </form>
    </div>
  </div>
</div>
