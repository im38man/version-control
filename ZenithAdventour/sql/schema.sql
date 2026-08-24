-- =========================================================
-- Zenith Tour & Travel - Skema Database
-- Import file ini lewat phpMyAdmin di vPanel InfinityFree
-- =========================================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    is_master TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destination_slug VARCHAR(60) NOT NULL,
    destination_title VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, destination_slug),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    nama_pelanggan VARCHAR(100) NULL,
    email_pelanggan VARCHAR(150) NULL,
    kode_booking VARCHAR(20) NOT NULL UNIQUE,
    destinasi VARCHAR(150) NOT NULL,
    jumlah_peserta INT NOT NULL DEFAULT 1,
    telepon VARCHAR(30) NOT NULL,
    status ENUM('menunggu_pembayaran','menunggu_konfirmasi','dikonfirmasi','ditolak') NOT NULL DEFAULT 'menunggu_pembayaran',
    status_keberangkatan ENUM('belum_berangkat','proses','selesai') NOT NULL DEFAULT 'belum_berangkat',
    sumber ENUM('web','admin_wa') NOT NULL DEFAULT 'web',
    catatan_admin TEXT NULL,
    dibuat_oleh_admin INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dibuat_oleh_admin) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    nominal DECIMAL(12,2) NOT NULL,
    bukti_file VARCHAR(255) NOT NULL,
    catatan VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
    catatan_admin VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pesan_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sender ENUM('user','admin') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    destinasi VARCHAR(150) NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    comment TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CATATAN: Akun admin TIDAK dibuat lewat file SQL ini.
-- Setelah import schema ini, buka /admin/setup-admin.php SEKALI lewat browser
-- untuk membuat akun admin pertama Anda, lalu HAPUS file setup-admin.php
-- dari server segera setelah itu (instruksi lengkap ada di README-DEPLOY.md).
