-- Tabel tambahan untuk Checkout (Keranjang + Pesanan)
-- Jalankan setelah tabel users ada. Import di dalam database yang sama.

CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    product_image VARCHAR(255) DEFAULT NULL,
    price DECIMAL(12,2) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NULL,
    channel ENUM('website','whatsapp','tiktok','instagram','facebook','shopee') NOT NULL DEFAULT 'website',
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'transfer_manual',
    payment_proof VARCHAR(255) DEFAULT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('menunggu_pembayaran','menunggu_konfirmasi','dikonfirmasi','dikirim','selesai','dibatalkan')
        NOT NULL DEFAULT 'menunggu_pembayaran',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    product_image VARCHAR(255) DEFAULT NULL,
    price DECIMAL(12,2) NOT NULL,
    qty INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;
