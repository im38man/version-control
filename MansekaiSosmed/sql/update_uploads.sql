-- =========================================
-- Migration: Upload Dokumen per-user (bukan lagi localStorage)
-- Jalankan file ini SEKALI setelah mansekai.sql & update_per_user_data.sql di-import.
-- =========================================
-- Kamu tidak perlu USE mansekai_study di sini kalau di hosting gratis (InfinityFree dkk):
-- pastikan aja kamu sudah masuk/klik ke database kamu duluan di phpMyAdmin sebelum Import.
-- USE mansekai_study;

CREATE TABLE IF NOT EXISTS dokumen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_asli VARCHAR(255) NOT NULL,      -- nama file asli waktu diupload user
    nama_file VARCHAR(255) NOT NULL,      -- nama file unik di server (disk)
    ekstensi VARCHAR(10) NOT NULL,
    ukuran INT NOT NULL,                  -- ukuran file dalam bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Catatan:
-- - File dokumen fisik disimpan di /uploads/documents/{user_id}/
-- - File avatar profil fisik disimpan di /uploads/avatars/{user_id}.{ext}
--   dan path-nya disimpan di kolom profil.avatar (tabel sudah ada dari
--   update_per_user_data.sql, tidak perlu migrasi tambahan untuk itu).
