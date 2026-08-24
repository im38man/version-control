-- Jalankan file ini kalau database mansekai_study KAMU SUDAH ADA sebelumnya
-- (dari versi sebelum ada fitur pengajuan materi). Kalau baru import dari
-- mansekai.sql yang terbaru, file ini TIDAK PERLU dijalankan.

-- Kamu tidak perlu USE mansekai_study di sini kalau di hosting gratis (InfinityFree dkk):
-- pastikan aja kamu sudah masuk/klik ke database kamu duluan di phpMyAdmin sebelum Import.
-- USE mansekai_study;

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
