-- =====================================================
-- MIGRASI: fitur "Perpindahan Dana"
-- Jalankan ini di phpMyAdmin HANYA JIKA database kamu
-- sudah pernah diimport sebelumnya (sebelum update ini).
-- Kalau baru mau install dari nol, cukup import database.sql
-- yang baru (kolom ini sudah termasuk di dalamnya).
-- =====================================================

ALTER TABLE transactions
    ADD COLUMN transfer_id CHAR(32) NULL AFTER tx_date;
