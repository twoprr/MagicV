<?php
function magicvpn_location_emoji_title(array $s): string {
    $raw = (string)($s['title'] ?? $s['id'] ?? 'Локация');
    $low = mb_strtolower($raw);
    if (str_contains($low, 'de') || str_contains($low, 'герман') || str_contains($low, 'germany')) return '🇩🇪 Германия';
    if (str_contains($low, 'fr') || str_contains($low, 'франц') || str_contains($low, 'france')) return '🇫🇷 Франция';
    return $raw;
}
?>
<div class="magic-card p-4 p-lg-5">
  <span class="badge badge-soft rounded-pill mb-3">Статус</span>
  <h1 class="fw-bold mb-2">Статус локаций MagicVPN</h1>
  <p class="text-muted-blue mb-4">Публичная проверка доступности серверов. Технические детали доступны только администратору.</p>
  <div class="row g-3">
    <?php foreach($statuses as $s): ?>
      <?php $ok = !empty($s['ok']) || (!empty($s['xray_active']) && !empty($s['port_listen'])); ?>
      <div class="col-md-6">
        <div class="feature-card h-100">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div><h4><?=e(magicvpn_location_emoji_title($s))?></h4><div class="text-muted-blue"><?=e($s['domain'] ?? '')?></div></div>
            <span class="status-pill <?=$ok ? '' : 'off'?>"><?=$ok ? '✅ Работает' : '⚠️ Проверяется'?></span>
          </div>
          <p class="text-muted-blue mt-3 mb-0">Если локация временно работает нестабильно, используйте другую доступную локацию в приложении.</p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
