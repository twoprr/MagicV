<?php
return [
    'driver' => getenv('DB_DRIVER') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int)(getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_DATABASE') ?: 'magicvpn',
    'username' => getenv('DB_USERNAME') ?: 'magicvpn',
    'password' => getenv('DB_PASSWORD') ?: 'TQV7GG9022',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];
