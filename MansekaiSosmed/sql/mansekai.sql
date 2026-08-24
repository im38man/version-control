-- =========================================
-- Database: mansekai_study
-- =========================================
-- CATATAN PENTING buat hosting gratis (InfinityFree, dsb):
-- JANGAN jalankan "CREATE DATABASE" / "USE nama_db" di sini.
-- Kamu TIDAK punya izin bikin database baru lewat SQL, dan nama
-- database kamu BUKAN "mansekai_study" polos, tapi ada prefix
-- akun kamu (contoh: if0_xxxxxxx_mansekai_study) - cek nama
-- persisnya di config/koneksi.php ($DB_NAME) atau di panel hosting.
--
-- Cara import yang benar:
-- 1. Buat database kosong dulu lewat Control Panel InfinityFree
--    (MySQL Databases), atau pakai yang sudah dikasih otomatis.
-- 2. Buka phpMyAdmin, KLIK MASUK ke database itu dulu (di sidebar kiri).
-- 3. Baru klik tab "Import", pilih file ini, dan jalankan.
--
-- Kalau kamu pakai XAMPP/localhost, silakan aktifkan lagi baris
-- CREATE DATABASE & USE di bawah ini sesuai kebutuhan.
-- =========================================
-- CREATE DATABASE IF NOT EXISTS mansekai_study CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE mansekai_study;

-- Tabel user (admin & user biasa)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    username_changed_at DATETIME DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- Catatan: kalau database lama sudah ada duluan (dibuat sebelum fitur lupa
-- password ada), jalankan sql/update_forgot_password.sql biar kolom di atas
-- ditambahkan ke tabel users yang sudah ada.
-- Begitu juga kalau database lama belum punya kolom username_changed_at,
-- jalankan sql/update_username_cooldown.sql.

-- Tabel materi pembelajaran
CREATE TABLE IF NOT EXISTS materi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    deskripsi VARCHAR(255) DEFAULT NULL,
    konten TEXT NOT NULL,
    status ENUM('Selesai','Proses','Locked') NOT NULL DEFAULT 'Proses',
    dibuat_oleh INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabel pengajuan akses materi (user minta izin, admin approve/reject)
CREATE TABLE IF NOT EXISTS pengajuan_materi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materi_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unik_pengajuan (materi_id, user_id),
    FOREIGN KEY (materi_id) REFERENCES materi(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- Seed data
-- =========================================
-- Catatan: akun admin TIDAK di-seed lewat SQL supaya hash password-nya
-- benar-benar dibuat oleh PHP (password_hash) di server kamu.
-- Jalankan setup_admin.php SEKALI setelah import database ini untuk
-- membuat akun admin pertama. Setelah berhasil, HAPUS file setup_admin.php.

-- Contoh materi awal (dibuat_oleh diisi NULL dulu, nanti bisa diedit lewat admin.php)
INSERT INTO materi (judul, deskripsi, konten, status, dibuat_oleh) VALUES
('HTML & CSS Responsive Dasar', 'Pengenalan konsep dasar desain web responsif',
'Responsive Web Design (RWD) adalah pendekatan desain web yang membuat halaman tampil baik di berbagai perangkat.\n\n1. Viewport Meta Tag\nSelalu sertakan tag meta viewport agar browser menangani skala halaman dengan benar di perangkat seluler.\n\n2. Media Queries\nMedia Queries memungkinkan kita menerapkan gaya CSS berdasarkan lebar layar perangkat.\n\n3. Flexbox dan Grid Layout\nCSS Flexbox dan Grid membantu elemen beradaptasi otomatis tanpa merusak tata letak di layar kecil.',
'Selesai', NULL),
('JavaScript DOM & Interaktif', 'Manipulasi elemen halaman dengan JavaScript',
'DOM (Document Object Model) memungkinkan JavaScript membaca dan mengubah struktur HTML secara dinamis.\n\nContoh penggunaan document.getElementById() dan addEventListener() untuk membuat halaman menjadi interaktif.',
'Proses', NULL),
('Pengantar Keamanan Web & Jaringan', 'Dasar-dasar keamanan aplikasi web',
'Materi ini membahas dasar keamanan web seperti SQL Injection, XSS, dan pentingnya validasi input di sisi server.',
'Locked', NULL);
