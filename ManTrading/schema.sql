-- ==========================================================
-- ManTrading - Skema Database MySQL
-- File ini otomatis di-import oleh setup.php saat instalasi pertama.
-- Kalau mau import manual (phpMyAdmin/CLI), pastikan database sudah
-- dibuat duluan di hosting (InfinityFree TIDAK izinkan CREATE DATABASE
-- lewat script, database wajib dibuat dulu di panel hosting).
-- ==========================================================

-- ---------------------------------------------------------
-- USERS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  vip_status ENUM('none','pending','approved','rejected') NOT NULL DEFAULT 'none',
  mentor_status ENUM('none','pending','approved','rejected') NOT NULL DEFAULT 'none',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Catatan: akun admin pertama DIBUAT LEWAT setup.php (bukan hardcode di sini),
-- supaya email & password admin bisa lu tentukan sendiri saat instalasi.

-- ---------------------------------------------------------
-- JOURNAL TRADES
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS trades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  trade_date DATE NOT NULL,
  pair VARCHAR(30) NOT NULL,
  entry DECIMAL(18,5) NOT NULL,
  sl DECIMAL(18,5) NULL,
  tp DECIMAL(18,5) NULL,
  lot DECIMAL(10,2) NOT NULL,
  pnl_status ENUM('Pending','Profit','Loss','Breakeven') NOT NULL DEFAULT 'Pending',
  pips DECIMAL(10,2) NULL,
  usd DECIMAL(14,2) NULL,
  image_before VARCHAR(500) NULL,
  image_after VARCHAR(500) NULL,
  tp1_hit TINYINT(1) NOT NULL DEFAULT 0,
  tp2_hit TINYINT(1) NOT NULL DEFAULT 0,
  tp3_hit TINYINT(1) NOT NULL DEFAULT 0,
  tp4_hit TINYINT(1) NOT NULL DEFAULT 0,
  tp5_hit TINYINT(1) NOT NULL DEFAULT 0,
  tp1_price DECIMAL(18,5) NULL, -- harga zona TP1 (RR 1:1)
  tp2_price DECIMAL(18,5) NULL, -- harga zona TP2 (RR 1:2)
  tp3_price DECIMAL(18,5) NULL, -- harga zona TP3 (RR 1:3)
  tp4_price DECIMAL(18,5) NULL, -- harga zona TP4 (RR 1:4)
  tp5_price DECIMAL(18,5) NULL, -- harga zona TP5 (RR 1:5)
  tp1_pips DECIMAL(10,2) NULL, tp1_usd DECIMAL(14,2) NULL,
  tp2_pips DECIMAL(10,2) NULL, tp2_usd DECIMAL(14,2) NULL,
  tp3_pips DECIMAL(10,2) NULL, tp3_usd DECIMAL(14,2) NULL,
  tp4_pips DECIMAL(10,2) NULL, tp4_usd DECIMAL(14,2) NULL,
  tp5_pips DECIMAL(10,2) NULL, tp5_usd DECIMAL(14,2) NULL,
  sl1_hit TINYINT(1) NOT NULL DEFAULT 0,
  sl2_hit TINYINT(1) NOT NULL DEFAULT 0,
  sl3_hit TINYINT(1) NOT NULL DEFAULT 0,
  sl4_hit TINYINT(1) NOT NULL DEFAULT 0,
  sl5_hit TINYINT(1) NOT NULL DEFAULT 0,
  sl1_price DECIMAL(18,5) NULL, -- SL berlaku di fase 1:1 (sebelum/at TP1)
  sl2_price DECIMAL(18,5) NULL, -- SL digeser di fase 1:2 (misal ke BE setelah TP1 kena)
  sl3_price DECIMAL(18,5) NULL, -- SL digeser di fase 1:3 (setelah TP2 kena)
  sl4_price DECIMAL(18,5) NULL, -- SL digeser di fase 1:4 (setelah TP3 kena)
  sl5_price DECIMAL(18,5) NULL, -- SL digeser di fase 1:5 (setelah TP4 kena)
  sl1_pips DECIMAL(10,2) NULL, sl1_usd DECIMAL(14,2) NULL,
  sl2_pips DECIMAL(10,2) NULL, sl2_usd DECIMAL(14,2) NULL,
  sl3_pips DECIMAL(10,2) NULL, sl3_usd DECIMAL(14,2) NULL,
  sl4_pips DECIMAL(10,2) NULL, sl4_usd DECIMAL(14,2) NULL,
  sl5_pips DECIMAL(10,2) NULL, sl5_usd DECIMAL(14,2) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- COMMUNITY POSTS (foto WAJIB upload file, bukan link)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS community_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  caption TEXT NOT NULL,
  image_path VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS community_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS community_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  parent_id INT NULL, -- diisi kalau komentar ini balasan (reply) ke komentar lain
  user_id INT NOT NULL,
  comment_text VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES community_comments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS community_comment_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  comment_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_comment_like (comment_id, user_id),
  FOREIGN KEY (comment_id) REFERENCES community_comments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- VIP CLASS + REQUEST AKSES (harus diajukan & disetujui admin)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS vip_classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  category VARCHAR(100) NOT NULL,
  image VARCHAR(500) NULL,
  description TEXT NULL,
  level VARCHAR(50) NULL,
  duration VARCHAR(50) NULL,
  content_url VARCHAR(255) NULL, -- link halaman materi, mis. "materi/materi1.php"
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vip_class_modules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vip_class_id INT NOT NULL,
  module_text VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (vip_class_id) REFERENCES vip_classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vip_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  message VARCHAR(500) NULL,
  admin_note VARCHAR(500) NULL,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  responded_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PENGAJUAN MENTOR (user ajukan jadi mentor, admin approve/reject.
-- Mentor yang di-approve boleh posting di Community.)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS mentor_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  message VARCHAR(500) NULL,
  admin_note VARCHAR(500) NULL,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  responded_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed 4 kelas VIP dari desain awal
INSERT INTO vip_classes (title, category, image, description, level, duration, content_url, sort_order) VALUES
('Gold Scalping Elite', 'XAUUSD Specialist', 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=800&q=80', 'Kuasai teknik scalping emas akurasi tinggi menggunakan price action murni pada timeframe M1 dan M5 untuk tangkap profit harian.', 'Advanced', '4 Minggu', 'materi/materi1.php', 1),
('Cara Profit Konsisten', 'Mindset & Risk Management', 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?auto=format&fit=crop&w=800&q=80', 'Belajar merubah pola pikir trader ritel menjadi institutional trader. Fokus pada konsistensi pertumbuhan equity jangka panjang.', 'All Levels', '3 Minggu', NULL, 2),
('Smart Money Concepts (SMC)', 'Institutional Strategy', 'https://images.unsplash.com/photo-1642543492481-44e81e3914a7?auto=format&fit=crop&w=800&q=80', 'Bedah tuntas cara smart money atau bank besar menggerakkan market. Pelajari Order Block, Fair Value Gap (FVG), dan BOS.', 'Intermediate', '5 Minggu', NULL, 3),
('Mastering Supply & Demand', 'Price Action Core', 'https://images.unsplash.com/photo-1621416894569-0f39ed31d247?auto=format&fit=crop&w=800&q=80', 'Strategi klasik paling ampuh tanpa indikator rumit. Temukan zona demand kuat untuk buy dan zone supply tajam untuk sell.', 'Beginner - Pro', '4 Minggu', NULL, 4);

INSERT INTO vip_class_modules (vip_class_id, module_text, sort_order) VALUES
(1, 'Pola Market Structure & Liquidity Sweep di XAUUSD', 1),
(1, 'Manajemen Risiko & Lot Sizing Khusus Akun Kecil', 2),
(1, 'Jam Sesi Terbaik (London & New York Killzone)', 3),
(2, 'Psikologi Trading & Mengatasi Revenge Trade', 1),
(2, 'Menyusun Trading Plan yang Disiplin & Terukur', 2),
(2, 'Evaluasi Jurnal Harian untuk Menekan Drawdown', 3),
(3, 'Mengenal Order Block & Mitigation Block Valid', 1),
(3, 'Memanfaatkan Fair Value Gap (FVG) sebagai Area Entry', 2),
(3, 'Menentukan Target TP Menggunakan Market Liquidity', 3),
(4, 'Cara Validasi Zona Fresh Supply & Demand', 1),
(4, 'Drop-Base-Rally & Rally-Base-Drop Pattern', 2),
(4, 'Filter Fakeout di Area Zona Kuat', 3);
