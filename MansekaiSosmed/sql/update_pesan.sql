-- =========================================
-- Migration: Fitur Pesan Antar User
-- Jalankan file ini SEKALI setelah mansekai.sql di-import.
-- Nambah 1 tabel baru `messages` yang nyambung ke users.id -
-- tabel lain (users, profil, follows, dst) tidak diubah sama sekali.
--
-- Kamu tidak perlu USE mansekai_study di sini kalau di hosting gratis (InfinityFree dkk):
-- pastikan aja kamu sudah masuk/klik ke database kamu duluan di phpMyAdmin sebelum Import.
-- USE mansekai_study;
-- =========================================

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    pesan TEXT NOT NULL,
    dibaca TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_percakapan (sender_id, receiver_id),
    INDEX idx_belum_dibaca (receiver_id, dibaca)
) ENGINE=InnoDB;