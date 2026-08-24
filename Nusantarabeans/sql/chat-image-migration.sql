-- Migrasi: dukungan kirim foto di fitur pesan/chat (user <-> admin)
-- Jalankan SEKALI saja di phpMyAdmin (database InfinityFree yang sudah berjalan).
-- Aman dijalankan tanpa menghapus data pesan yang sudah ada.

ALTER TABLE messages
    ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER message;
