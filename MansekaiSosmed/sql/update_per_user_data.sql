-- =========================================
-- Migration: Data per-user untuk Notepad, Arus Kas,
-- Pengingat, Profil, dan Dashboard.
-- Jalankan file ini SEKALI setelah mansekai.sql di-import.
-- Semua tabel di bawah terhubung ke users.id, jadi setiap
-- user (dan admin) punya datanya sendiri-sendiri.
-- =========================================
-- Kamu tidak perlu USE mansekai_study di sini kalau di hosting gratis (InfinityFree dkk):
-- pastikan aja kamu sudah masuk/klik ke database kamu duluan di phpMyAdmin sebelum Import.
-- USE mansekai_study;

-- Catatan bebas (Notepad)
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL DEFAULT 'Catatan Baru',
    isi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Transaksi Arus Kas
CREATE TABLE IF NOT EXISTS cashflow (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    deskripsi VARCHAR(255) NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    tipe ENUM('masuk','keluar') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Pengingat Hari / Alarm
CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    waktu DATETIME NOT NULL,
    notif_terkirim TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Profil tambahan (avatar, bio, sosial media) - 1 baris per user
CREATE TABLE IF NOT EXISTS profil (
    user_id INT PRIMARY KEY,
    avatar VARCHAR(500) DEFAULT NULL,
    bio VARCHAR(255) DEFAULT NULL,
    link_github VARCHAR(255) DEFAULT NULL,
    link_linkedin VARCHAR(255) DEFAULT NULL,
    link_instagram VARCHAR(255) DEFAULT NULL,
    link_tiktok VARCHAR(255) DEFAULT NULL,
    link_facebook VARCHAR(255) DEFAULT NULL,
    link_x VARCHAR(255) DEFAULT NULL,
    link_youtube VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Todo-list di Dashboard
CREATE TABLE IF NOT EXISTS dash_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    teks VARCHAR(255) NOT NULL,
    selesai TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Modul Terbaru di Dashboard
CREATE TABLE IF NOT EXISTS dash_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    status ENUM('Selesai','Proses','Locked') NOT NULL DEFAULT 'Proses',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Jadwal Belajar Harian di Dashboard
CREATE TABLE IF NOT EXISTS dash_jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    jam VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Target Pembelajaran di Dashboard
CREATE TABLE IF NOT EXISTS dash_target (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    persen TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Winstreak belajar - 1 baris per user
CREATE TABLE IF NOT EXISTS dash_streak (
    user_id INT PRIMARY KEY,
    streak_count INT NOT NULL DEFAULT 0,
    last_claim_date DATE DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
