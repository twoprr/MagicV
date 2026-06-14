<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Баланс</span>
      <h1 class="fw-bold mb-0">Балансы пользователей</h1>
      <div class="text-muted-blue mt-2">Ручное начисление и списание средств.</div>
    </div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>

  <form class="row g-2 mb-4" method="get">
    <div class="col-md-8"><input class="form-control" name="q" value="<?=e($q ?? '')?>" placeholder="Поиск по email или имени"></div>
    <div class="col-md-4"><button class="btn btn-magic w-100">Найти</button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
      <thead><tr><th>ID</th><th>Email</th><th>Имя</th><th>Баланс</th><th>Изменить</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=e((string)$r['id'])?></td>
          <td><?=e($r['email'])?></td>
          <td><?=e((string)$r['name'])?></td>
          <td><b><?=e((string)$r['balance'])?> ₽</b></td>
          <td>
            <form method="post" action="/admin/balances/adjust" class="d-flex gap-2 flex-wrap">
              <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
              <input type="hidden" name="user_id" value="<?=(int)$r['id']?>">
              <input class="form-control form-control-sm" style="max-width:120px" type="number" name="amount" placeholder="+500 / -100" required>
              <input class="form-control form-control-sm" style="max-width:260px" name="comment" placeholder="Комментарий">
              <button class="btn btn-outline-magic btn-sm">Сохранить</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
