CREATE TABLE IF NOT EXISTS users (
  login TEXT PRIMARY KEY,
  password TEXT NOT NULL
);

INSERT INTO users (login, password) VALUES ('alice', 'secret')
ON CONFLICT (login) DO UPDATE SET password = EXCLUDED.password;
