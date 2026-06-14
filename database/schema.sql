CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  name TEXT,
  telegram_id INTEGER,
  bot_user_id INTEGER,
  role TEXT NOT NULL DEFAULT 'user',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_users_telegram_id ON users(telegram_id);

CREATE TABLE IF NOT EXISTS orders (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  days INTEGER NOT NULL,
  amount INTEGER NOT NULL,
  status TEXT NOT NULL DEFAULT 'pending',
  receipt_path TEXT,
  last_error TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS subscriptions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  server_id TEXT NOT NULL DEFAULT 'global',
  uuid TEXT NOT NULL,
  active INTEGER NOT NULL DEFAULT 1,
  expires_at TEXT NOT NULL,
  reminded_3 INTEGER NOT NULL DEFAULT 0,
  reminded_1 INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_subscriptions_global ON subscriptions(user_id, server_id, expires_at);

CREATE TABLE IF NOT EXISTS server_health (
  server_id TEXT PRIMARY KEY,
  last_ok_at TEXT,
  last_error_at TEXT,
  last_error_text TEXT
);
