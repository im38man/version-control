-- =========================================================
-- Migrasi: Fitur Pemberangkatan & Testimoni
-- Jalankan file ini di phpMyAdmin (tab SQL) HANYA jika database
-- Anda sudah pernah dibuat sebelumnya (sudah ada tabel bookings, dst).
-- Jika ini instalasi baru, cukup import sql/schema.sql saja (tidak perlu file ini).
-- =========================================================

ALTER TABLE bookings
    ADD COLUMN status_keberangkatan ENUM('belum_berangkat','proses','selesai') NOT NULL DEFAULT 'belum_berangkat' AFTER status;

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
