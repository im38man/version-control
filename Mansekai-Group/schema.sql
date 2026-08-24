-- =========================================================
-- Mansekai Group Dashboard - Database Schema
-- Import file ini lewat phpMyAdmin di InfinityFree Control Panel
-- =========================================================

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_title VARCHAR(150) DEFAULT 'Team Member',
    avatar VARCHAR(500) DEFAULT NULL,
    recovery_email VARCHAR(150) DEFAULT NULL,
    recovery_phone VARCHAR(50) DEFAULT NULL,
    socials TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_username (username),
    UNIQUE KEY uniq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Menyimpan tiap "modul" data dashboard (projects, team, companies, dst)
-- sebagai JSON per user. Pendekatan ini dipilih karena struktur data asli
-- (localStorage) sudah berbentuk array/object bersarang (nested), sehingga
-- paling aman & minim bug dipindah apa adanya ke MySQL sebagai JSON per user,
-- lalu tiap user hanya bisa baca/tulis baris miliknya sendiri (owner_id).
CREATE TABLE IF NOT EXISTS user_data (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED NOT NULL,
    data_key VARCHAR(50) NOT NULL,
    data_value LONGTEXT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_owner_key (owner_id, data_key),
    CONSTRAINT fk_user_data_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_token (token),
    CONSTRAINT fk_pwreset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
