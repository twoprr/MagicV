CREATE TABLE IF NOT EXISTS plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  days INT NOT NULL,
  price INT NOT NULL,
  old_price INT NULL,
  badge VARCHAR(100) NULL,
  description TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_popular TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 100,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payment_providers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  title VARCHAR(190) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  config_json TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_id INT NULL,
  plan_id INT NULL,
  provider_code VARCHAR(64) NOT NULL,
  external_id VARCHAR(190) NULL,
  amount INT NOT NULL,
  currency VARCHAR(16) NOT NULL DEFAULT 'RUB',
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  pay_url TEXT NULL,
  raw_request MEDIUMTEXT NULL,
  raw_response MEDIUMTEXT NULL,
  last_error TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  INDEX idx_payments_user_id (user_id),
  INDEX idx_payments_status (status),
  INDEX idx_payments_external_id (external_id),
  INDEX idx_payments_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE orders ADD COLUMN IF NOT EXISTS plan_id INT NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_id INT NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_provider VARCHAR(64) NULL;
