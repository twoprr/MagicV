<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Продажи</span>
      <h2 class="fw-bold mb-0">Платежи</h2>
      <div class="text-muted-blue mt-2">История платежей от ручной оплаты, CryptoCloud и AAIO.</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-magic" href="/admin/payment-providers">Провайдеры</a>
      <a class="btn btn-outline-magic" href="/admin">Назад</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Пользователь</th>
          <th>Сумма</th>
          <th>Провайдер</th>
          <th>Статус</th>
          <th>External ID</th>
          <th>Дата</th>
          <th>Оплачено</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td><?=e((string)$p['id'])?></td>
          <td><?=e($p['email'] ?? ('#'.($p['user_id'] ?? '')))?></td>
          <td><?=e((string)$p['amount'])?> <?=e($p['currency'] ?? 'RUB')?></td>
          <td><?=e($p['provider_code'])?></td>
          <td><span class="badge <?=($p['status'] ?? '')==='paid'?'bg-success':(($p['status'] ?? '')==='failed'?'bg-danger':'bg-warning')?>"><?=e($p['status'])?></span></td>
          <td><?=!empty($p['external_id']) ? e($p['external_id']) : '—'?></td>
          <td><?=e($p['created_at'] ?? '')?></td>
          <td><?=!empty($p['paid_at']) ? e($p['paid_at']) : '—'?></td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($payments)): ?>
        <tr><td colspan="8" class="text-center text-muted-blue py-4">Платежей пока нет.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
