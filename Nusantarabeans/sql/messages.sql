-- Tabel pesan (chat) antara user dan admin
-- Jalankan setelah tabel users ada, di database yang sama.

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,              -- id user (pelanggan) pemilik percakapan
    sender_role ENUM('user','admin') NOT NULL,
    sender_id INT NOT NULL,            -- id akun pengirim (user atau admin yang membalas)
    message TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,  -- foto terlampir (opsional, maks 1MB)
    sender_location VARCHAR(150) DEFAULT NULL, -- daerah/kota pengirim, dideteksi otomatis dari GPS browser
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
