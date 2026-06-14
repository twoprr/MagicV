CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(190) NULL,
  telegram_id BIGINT NULL,
  bot_user_id BIGINT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_telegram_id (telegram_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  days INT NOT NULL,
  amount INT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  receipt_path VARCHAR(255) NULL,
  last_error TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_user_id (user_id),
  INDEX idx_orders_status (status),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  server_id VARCHAR(64) NOT NULL DEFAULT 'global',
  uuid VARCHAR(64) NOT NULL,
  sub_token VARCHAR(96) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  expires_at DATETIME NOT NULL,
  reminded_3 TINYINT(1) NOT NULL DEFAULT 0,
  reminded_1 TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sub_user_id (user_id),
  INDEX idx_sub_server_id (server_id),
  INDEX idx_sub_active_expires (active, expires_at),
  INDEX idx_subscriptions_global (user_id, server_id, expires_at),
  INDEX idx_subscriptions_token (sub_token),
  CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS server_health (
  server_id VARCHAR(64) PRIMARY KEY,
  last_ok_at DATETIME NULL,
  last_error_at DATETIME NULL,
  last_error_text TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS traffic_stats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  server_id VARCHAR(64) NOT NULL,
  ts DATETIME NOT NULL,
  uplink_bytes BIGINT NOT NULL DEFAULT 0,
  downlink_bytes BIGINT NOT NULL DEFAULT 0,
  INDEX idx_traffic_user_id (user_id),
  INDEX idx_traffic_server_id (server_id),
  INDEX idx_traffic_ts (ts)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_user_id INT NULL,
  action VARCHAR(80) NOT NULL,
  target_user_id INT NULL,
  details TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admin_logs_created_at (created_at),
  INDEX idx_admin_logs_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upgrade: promos, trial, login attempts, email logs
ALTER TABLE users ADD COLUMN IF NOT EXISTS trial_used TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verify_token VARCHAR(128) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verify_expires_at DATETIME NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS promo_code VARCHAR(64) NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS original_amount INT NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS discount_amount INT NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS promo_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  type VARCHAR(20) NOT NULL,
  value INT NOT NULL,
  max_uses INT NULL,
  used_count INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_promo_codes_code (code),
  INDEX idx_promo_codes_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promo_uses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  promo_id INT NOT NULL,
  user_id INT NOT NULL,
  order_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_promo_user (promo_id, user_id),
  INDEX idx_promo_uses_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  ip VARCHAR(64) NOT NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_attempts_email_ip (email, ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  status VARCHAR(32) NOT NULL,
  error_text TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(120) PRIMARY KEY,
  setting_value TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_notices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  target_type VARCHAR(20) NOT NULL DEFAULT 'all',
  user_id INT NULL,
  title VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  level VARCHAR(20) NOT NULL DEFAULT 'info',
  pinned TINYINT(1) NOT NULL DEFAULT 1,
  active TINYINT(1) NOT NULL DEFAULT 1,
  starts_at DATETIME NULL,
  expires_at DATETIME NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notices_active (active, starts_at, expires_at),
  INDEX idx_notices_target (target_type, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
