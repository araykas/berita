-- ==========================================
-- DATABASE SCHEMA WEB BERITA (FINAL UPDATE)
-- SQLite
-- ==========================================

PRAGMA foreign_keys = ON;

-- ==========================================
-- TABEL USERS
-- ==========================================
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user' CHECK(role IN ('user','admin')),
    profile_image TEXT,          -- path atau URL gambar profil
    banned_until DATETIME,       -- ban sementara user
    is_active INTEGER DEFAULT 1, -- 1=aktif, 0=nonaktif
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- TABEL KATEGORI BERITA
-- ==========================================
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

-- ==========================================
-- TABEL BERITA
-- ==========================================
CREATE TABLE news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    excerpt TEXT,                -- cuplikan berita untuk index
    content TEXT NOT NULL,
    image TEXT,                  -- path atau URL gambar berita
    category_id INTEGER,
    author_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY(author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ==========================================
-- TABEL KOMENTAR
-- ==========================================
CREATE TABLE comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    news_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(news_id) REFERENCES news(id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- TABEL RIWAYAT BACA BERITA
-- ==========================================
CREATE TABLE read_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    news_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(news_id) REFERENCES news(id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- TABEL RESET PASSWORD
-- ==========================================
CREATE TABLE password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token TEXT NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
