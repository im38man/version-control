-- =========================================
-- Migration: Fitur Lupa Password & Reset Password
-- Jalankan file ini SEKALI setelah mansekai.sql di-import.
-- Menambahkan kolom email (buat kirim link reset password)
-- dan kolom token untuk proses reset.
-- =========================================
-- Kamu tidak perlu USE mansekai_study di sini kalau di hosting gratis (InfinityFree dkk):
-- pastikan aja kamu sudah masuk/klik ke database kamu duluan di phpMyAdmin sebelum Import.
-- USE mansekai_study;

ALTER TABLE users
    ADD COLUMN email VARCHAR(150) DEFAULT NULL UNIQUE AFTER username,
    ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL AFTER password,
    ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL AFTER reset_token;
