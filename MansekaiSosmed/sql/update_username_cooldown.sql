-- =========================================
-- Migration: Cooldown Ganti Username (30 hari)
-- Jalankan file ini SEKALI setelah mansekai.sql di-import.
-- Menambahkan kolom buat nyimpen kapan terakhir kali user
-- ganti username, biar bisa dibatasi 30 hari sekali.
-- =========================================
-- Kamu tidak perlu USE mansekai_study di sini kalau di hosting gratis (InfinityFree dkk):
-- pastikan aja kamu sudah masuk/klik ke database kamu duluan di phpMyAdmin sebelum Import.
-- USE mansekai_study;

ALTER TABLE users
    ADD COLUMN username_changed_at DATETIME DEFAULT NULL AFTER username;
