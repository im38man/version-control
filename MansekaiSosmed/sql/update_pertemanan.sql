-- =========================================
-- Migration: Fitur Pertemanan (Follow Antar User)
-- Jalankan file ini SEKALI setelah mansekai.sql di-import.
-- Tabel ini TIDAK mengubah tabel users atau tabel lain yang sudah
-- ada -- cuma nambah 1 tabel baru yang nyambung ke users.id.
--
-- Kamu tidak perlu USE mansekai_study di sini kalau di hosting gratis (InfinityFree dkk):
-- pastikan aja kamu sudah masuk/klik ke database kamu duluan di phpMyAdmin sebelum Import.
-- USE mansekai_study;
-- =========================================

CREATE TABLE IF NOT EXISTS follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,   -- user yang MELAKUKAN follow
    following_id INT NOT NULL,  -- user yang DI-FOLLOW
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
