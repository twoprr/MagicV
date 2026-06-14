<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><span class="badge badge-soft rounded-pill mb-2">Админка</span><h2 class="fw-bold mb-0">Пользователи</h2></div><a class="btn btn-outline-magic" href="/admin">Назад</a></div>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>№</th><th>Email / ник</th><th>Telegram ID</th><th>Окончание</th><th>Статус</th></tr></thead><tbody>
  <?php foreach($rows as $i=>$r): $active=($r['active'] && $r['expires_at'] && strtotime($r['expires_at'])>time()); ?><tr><td><?=$i+1?></td><td><?=e($r['name'] ?: $r['email'])?><br><span class="text-muted-blue small"><?=e($r['email'])?></span></td><td><?=e((string)($r['telegram_id'] ?? '—'))?></td><td><?=$r['expires_at']?e(date('d.m.Y H:i',strtotime($r['expires_at']))):'—'?></td><td><span class="status-pill <?=$active?'':'off'?>"><?=$active?'Активна':'Неактивна'?></span></td></tr><?php endforeach; ?>
  </tbody></table></div>
</div>
