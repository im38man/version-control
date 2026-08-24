-- Jalankan ini di dalam database yang SUDAH kamu buat lewat panel InfinityFree
-- (pilih database itu dulu di phpMyAdmin, lalu buka tab SQL, tempel isi file ini)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    recovery_email VARCHAR(150) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin_master','admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
