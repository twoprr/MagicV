<?php
function magicvpn_admin_location_emoji_title(array $r): string {
    $raw = (string)($r['title'] ?? $r['id'] ?? 'Локация');
    $low = mb_strtolower($raw);
    if (str_contains($low, 'de') || str_contains($low, 'герман') || str_contains($low, 'germany')) return '🇩🇪 Германия';
    if (str_contains($low, 'fr') || str_contains($low, 'франц') || str_contains($low, 'france')) return '🇫🇷 Франция';
    return $raw;
}
?>
<div class="magic-card p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h2 class="mb-1">Статус серверов</h2>
      <p class="text-muted-blue mb-0">Расширенная проверка Xray, портов, inbound tags, клиентов, интернета и диска.</p>
    </div>
    <a class="btn btn-outline-magic" href="/admin/status">Обновить</a>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Профиль</th>
          <th>Порт</th>
          <th>Xray</th>
          <th>Listen</th>
          <th>Inbound</th>
          <th>Клиенты</th>
          <th>Интернет</th>
          <th>Диск</th>
          <th>Load</th>
          <th>Ошибка</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($statuses as $r): ?>
          <?php
            $tagText = !empty($r['tag_found'])
                ? '✅ tag'
                : (!empty($r['actual_tag']) ? '⚠️ port: ' . $r['actual_tag'] : '⛔️ нет');
            $err = $r['error'] ?? $r['config_error'] ?? $r['active_error'] ?? $r['internet_error'] ?? '';
          ?>
          <tr>
            <td>
              <div class="fw-semibold"><?=e(magicvpn_admin_location_emoji_title($r))?></div>
              <div class="small text-muted-blue">ожидался: <?=e((string)($r['expected_tag'] ?? ''))?></div>
              <?php if(!empty($r['actual_tag']) && ($r['actual_tag'] !== ($r['expected_tag'] ?? ''))): ?>
                <div class="small text-warning">реальный tag: <?=e((string)$r['actual_tag'])?></div>
              <?php endif; ?>
            </td>
            <td><?=e((string)$r['port'])?></td>
            <td><?=!empty($r['active']) ? '✅ active' : '⛔️'?></td>
            <td><?=!empty($r['listening']) ? '✅ listen' : '⛔️'?></td>
            <td><?=$tagText?></td>
            <td><?=e((string)($r['clients'] ?? 0))?></td>
            <td><?=!empty($r['internet']) ? '✅ ok' : '⛔️'?></td>
            <td><?=isset($r['disk_free_gb']) ? e((string)$r['disk_free_gb']) . ' GB' : '—'?></td>
            <td><?=e((string)($r['loadavg'] ?? '—'))?></td>
            <td class="small text-warning" style="max-width:360px;white-space:normal;"><?=e((string)$err)?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="alert alert-info rounded-4 mt-3 mb-0">
    Если клиентов показывает 0, смотри колонку <b>реальный tag</b>. Часто причина — в <code>config/servers.php</code> указан один <code>inbound_tag</code>, а на Xray-сервере фактически другой.
  </div>
</div>
