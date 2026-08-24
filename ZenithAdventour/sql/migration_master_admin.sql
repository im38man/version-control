-- =========================================================
-- Migrasi: Fitur Admin Master
-- Jalankan file ini di phpMyAdmin (tab SQL) HANYA jika database
-- Anda sudah pernah dibuat sebelumnya.
-- Jika ini instalasi baru, cukup import sql/schema.sql saja.
-- =========================================================

ALTER TABLE users
    ADD COLUMN is_master TINYINT(1) NOT NULL DEFAULT 0 AFTER role;

-- Jadikan akun admin PERTAMA (yang paling lama dibuat) sebagai Admin Master.
-- Hanya Admin Master yang bisa menghapus user & menjadikan user sebagai admin.
UPDATE users
SET is_master = 1
WHERE role = 'admin'
ORDER BY id ASC
LIMIT 1;
