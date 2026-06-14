<div class="magic-card p-4">
  <h2>Импорт из Telegram-бота</h2>
  <p class="text-muted-blue">Импорт выполняется из консоли, чтобы не зависеть от таймаутов браузера и прав доступа к SQLite.</p>
  <div class="stat">
    <p class="mb-2">Команда:</p>
    <pre class="copy-box mb-0">php scripts/import_bot_sqlite.php /opt/vpn_shop_bot/database/db.sqlite3</pre>
  </div>
  <p class="text-muted-blue mt-3 mb-0">Скрипт перенесёт пользователей Telegram и их максимальную активную подписку в формат сайта: <code>server_id = global</code>.</p>
</div>
