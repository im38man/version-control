-- =========================================================
-- Migrasi: Fitur Kelola Booking via WhatsApp (input manual admin)
-- Jalankan file ini di phpMyAdmin (tab SQL) HANYA jika database
-- Anda sudah pernah dibuat sebelumnya.
-- Jika ini instalasi baru, cukup import sql/schema.sql saja.
-- =========================================================

ALTER TABLE bookings
    MODIFY COLUMN user_id INT NULL,
    ADD COLUMN nama_pelanggan VARCHAR(100) NULL AFTER user_id,
    ADD COLUMN email_pelanggan VARCHAR(150) NULL AFTER nama_pelanggan,
    ADD COLUMN sumber ENUM('web','admin_wa') NOT NULL DEFAULT 'web' AFTER status_keberangkatan,
    ADD COLUMN catatan_admin TEXT NULL AFTER sumber,
    ADD COLUMN dibuat_oleh_admin INT NULL AFTER catatan_admin,
    ADD CONSTRAINT fk_bookings_dibuat_oleh_admin FOREIGN KEY (dibuat_oleh_admin) REFERENCES users(id) ON DELETE SET NULL;
