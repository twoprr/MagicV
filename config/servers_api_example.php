<?php
// Добавьте эти поля в каждый сервер/профиль в config/servers.php:
return [
    'provision_mode' => 'api',
    'xray_api_server' => '127.0.0.1:10085',
    'xray_api_tag' => 'api',
    'xray_binary' => 'xray',
    // Больше НЕ ставим restart на каждую покупку:
    'xray_reload_cmd' => 'true',
];
