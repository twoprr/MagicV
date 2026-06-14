<?php
return [
    'app_name' => 'MagicVPN',
    'base_url' => getenv('APP_URL') ?: '',
    'admin_email' => getenv('ADMIN_EMAIL') ?: 'admin@magicvpn.local',
    'admin_password' => getenv('ADMIN_PASSWORD') ?: 'ChangeMe123!',
    'brand' => 'MagicVPN',
    'prices' => [
        7 => 199,
        30 => 499,
        90 => 1199,
        180 => 1999,
    ],
    'payment' => [
        'phone' => getenv('PAYMENT_PHONE') ?: '+7 XXX XXX-XX-XX',
        'bank' => getenv('PAYMENT_BANK') ?: 'Банк',
        'holder' => getenv('PAYMENT_HOLDER') ?: 'Получатель',
    ],
    'trial' => [
        'enabled' => getenv('TRIAL_ENABLED') !== '0',
        'days' => (int)(getenv('TRIAL_DAYS') ?: 1),
    ],
    'mail' => [
        'enabled' => getenv('MAIL_ENABLED') === '1',
        'from' => getenv('MAIL_FROM') ?: 'no-reply@magicvpn.local',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'MagicVPN',
    ],
    'security' => [
        'max_login_attempts' => (int)(getenv('MAX_LOGIN_ATTEMPTS') ?: 5),
        'login_window_minutes' => (int)(getenv('LOGIN_WINDOW_MINUTES') ?: 15),
    ],
    'backup' => [
        'dir' => getenv('BACKUP_DIR') ?: '/var/backups/magicvpn',
        'keep_days' => (int)(getenv('BACKUP_KEEP_DAYS') ?: 14),
    ],
];
