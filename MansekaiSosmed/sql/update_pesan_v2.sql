-- =========================================
-- Migration: Hapus Pesan + Permintaan Pesan (Message Request)
-- Jalankan file ini SEKALI setelah sql/update_pesan.sql dan
-- sql/update_pertemanan.sql di-import.
--
-- Isi migrasi ini:
-- 1. Nambah 2 kolom ke tabel `messages` buat fitur hapus pesan
--    (hapus per-user / "hapus untuk saya", tanpa menghapus pesan
--    dari sisi lawan bicara).
-- 2. Tabel baru `permintaan_pesan` buat fitur "kirim pesan harus
--    lewat permintaan dulu" kalau kedua user belum saling follow.
--
-- USE mansekai_study;
-- =========================================

ALTER TABLE messages
    ADD COLUMN deleted_by_sender TINYINT(1) NOT NULL DEFAULT 0 AFTER dibaca,
    ADD COLUMN deleted_by_receiver TINYINT(1) NOT NULL DEFAULT 0 AFTER deleted_by_sender;

CREATE TABLE IF NOT EXISTS permintaan_pesan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengirim_id INT NOT NULL,   -- user yang minta izin buat chat
    penerima_id INT NOT NULL,   -- user yang harus menerima/menolak
    status ENUM('pending', 'diterima', 'ditolak') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pasangan (pengirim_id, penerima_id),
    FOREIGN KEY (pengirim_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (penerima_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_penerima_status (penerima_id, status)
) ENGINE=InnoDB;
