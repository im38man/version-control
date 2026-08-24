-- Migration: materi sekarang berbasis file .html yang diupload admin,
-- bukan lagi teks biasa di kolom konten.
-- Jalankan file ini SEKALI di database yang sudah ada (phpMyAdmin / mysql CLI).

ALTER TABLE materi
    MODIFY konten TEXT NULL,
    ADD COLUMN file_materi VARCHAR(255) DEFAULT NULL AFTER konten,
    ADD COLUMN file_asli VARCHAR(255) DEFAULT NULL AFTER file_materi;

-- Catatan:
-- - konten       -> sekarang opsional, dipakai sebagai fallback/ringkasan lama.
-- - file_materi  -> nama file fisik unik di server (folder uploads/materi/).
-- - file_asli    -> nama file asli waktu diupload admin (buat ditampilkan di UI).
