<div class="magic-card p-4 p-lg-5">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <span class="badge badge-soft rounded-pill mb-2">Уведомления</span>
      <h1 class="fw-bold mb-0">Закреплённые уведомления</h1>
      <div class="text-muted-blue mt-2">Показываются пользователям в кабинете и на всех внутренних страницах.</div>
    </div>
    <a class="btn btn-outline-magic" href="/admin">Назад</a>
  </div>

  <form method="post" action="/admin/notices/create" class="row g-3 mb-4">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
    <div class="col-md-4">
      <label class="form-label">Кому показать</label>
      <select name="target_type" class="form-select" id="targetType">
        <option value="all">Всем пользователям</option>
        <option value="user">Одному пользователю по email</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Email пользователя, если выбран один</label>
      <input name="target_email" class="form-control" placeholder="user@example.com">
    </div>
    <div class="col-md-4">
      <label class="form-label">Тип</label>
      <select name="level" class="form-select">
        <option value="info">Информация</option>
        <option value="warning">Предупреждение</option>
        <option value="danger">Авария</option>
        <option value="success">Успешно</option>
      </select>
    </div>
    <div class="col-md-8">
      <label class="form-label">Заголовок</label>
      <input name="title" class="form-control" required placeholder="Например: Возможны перебои на Франции">
    </div>
    <div class="col-md-4">
      <label class="form-label">Показывать до UTC, необязательно</label>
      <input name="expires_at" type="datetime-local" class="form-control">
    </div>
    <div class="col-12">
      <label class="form-label">Текст</label>
      <textarea name="body" class="form-control" rows="4" required placeholder="Опишите проблему, сроки восстановления или рекомендацию пользователям."></textarea>
    </div>
    <div class="col-12 d-flex gap-2 flex-wrap">
      <button class="btn btn-magic">Закрепить уведомление</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
      <thead><tr><th>ID</th><th>Кому</th><th>Заголовок</th><th>Тип</th><th>Активно</th><th>До</th><th></th></tr></thead>
      <tbody>
      <?php foreach($notices as $n): ?>
        <tr>
          <td><?=$n['id']?></td>
          <td><?=e($n['target_type']==='all'?'Все':($n['target_email'] ?? ('User #'.$n['user_id'])))?></td>
          <td><b><?=e($n['title'])?></b><div class="text-muted-blue small"><?=nl2br(e(mb_strimwidth($n['body'],0,130,'…')))?></div></td>
          <td><span class="badge text-bg-<?=e($n['level'])?>"><?=e($n['level'])?></span></td>
          <td><?=!empty($n['active'])?'✅':'⛔️'?></td>
          <td><?=e($n['expires_at'] ?: '—')?></td>
          <td class="text-end">
            <form method="post" action="/admin/notices/toggle" class="d-inline">
              <input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=$n['id']?>">
              <button class="btn btn-sm btn-outline-magic"><?=!empty($n['active'])?'Отключить':'Включить'?></button>
            </form>
            <form method="post" action="/admin/notices/delete" class="d-inline" onsubmit="return confirm('Удалить уведомление?')">
              <input type="hidden" name="_csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=$n['id']?>">
              <button class="btn btn-sm btn-outline-danger">Удалить</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
