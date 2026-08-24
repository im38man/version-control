-- Migrasi: dukungan pesanan manual dari WhatsApp, TikTok, Instagram, Facebook, Shopee
-- Jalankan SEKALI saja di phpMyAdmin (database InfinityFree yang sudah berjalan).
-- Aman dijalankan tanpa menghapus data pesanan yang sudah ada.

ALTER TABLE orders MODIFY user_id INT NULL;

ALTER TABLE orders
    ADD COLUMN channel ENUM('website','whatsapp','tiktok','instagram','facebook','shopee')
    NOT NULL DEFAULT 'website' AFTER order_code;
