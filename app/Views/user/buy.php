<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="magic-card p-4 p-lg-5">
      <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div><span class="badge badge-soft rounded-pill mb-2">Тарифы</span><h1 class="fw-bold mb-1">Купить / продлить MagicVPN</h1><p class="text-muted-blue mb-0">Одна подписка открывает доступ ко всем локациям.</p></div>
        <span class="status-pill">🇩🇪 Германия + 🇫🇷 Франция</span>
      </div>
      <div class="row g-3">
        <?php foreach($prices as $days=>$amount): ?>
          <div class="col-md-6 col-xl-3">
            <form method="post" action="/order/create" class="h-100">
              <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
              <input type="hidden" name="days" value="<?=e((string)$days)?>">
              <div class="plan-card feature-card h-100 d-flex flex-column">
                <h5>+<?=$days?> дней</h5>
                <div class="plan-price"><?=$amount?> ₽</div>
                <p class="text-muted-blue">Все серверы и профили MagicVPN.</p>
                <input class="form-control form-control-sm mb-3" name="promo_code" placeholder="Промокод, если есть">
                <button class="btn btn-magic w-100 mt-auto">Создать заявку</button>
              </div>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
