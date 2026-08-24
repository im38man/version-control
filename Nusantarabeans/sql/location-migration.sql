-- Migrasi: dukungan lokasi/daerah pengirim otomatis (GPS browser) di fitur pesan
-- Jalankan SEKALI saja di phpMyAdmin (database InfinityFree yang sudah berjalan).
-- Aman dijalankan tanpa menghapus data pesan yang sudah ada.

ALTER TABLE messages
    ADD COLUMN sender_location VARCHAR(150) DEFAULT NULL AFTER image_path;
