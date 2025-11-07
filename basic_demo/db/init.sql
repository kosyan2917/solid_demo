CREATE TABLE IF NOT EXISTS users (
  login TEXT PRIMARY KEY,
  password TEXT NOT NULL
);

INSERT INTO users (login, password) VALUES ('alice', 'secret')
ON CONFLICT (login) DO UPDATE SET password = EXCLUDED.password;

CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT
);
INSERT INTO products (name, description)
VALUES
    ('Ноутбук', 'Легкий ультрабук с экраном 14 дюймов'),
    ('Смартфон', 'АСмартфон с поддержкой 5G и OLED-экраном');
