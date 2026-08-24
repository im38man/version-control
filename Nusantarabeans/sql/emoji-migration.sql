-- Migrasi: perbaiki emoji yang jadi "?" saat dikirim di fitur pesan
-- Penyebab: tabel messages sebelumnya tidak eksplisit pakai utf8mb4, jadi emoji
-- (yang butuh penyimpanan 4-byte) tidak bisa disimpan dan berubah jadi "?".
-- Jalankan SEKALI saja di phpMyAdmin (database InfinityFree yang sudah berjalan).
-- Aman dijalankan tanpa menghapus data pesan yang sudah ada — tapi emoji yang
-- SUDAH terlanjur tersimpan sebagai "?" tidak bisa dikembalikan lagi (datanya
-- sudah rusak duluan), hanya pesan BARU setelah migrasi ini yang akan benar.

ALTER TABLE messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
