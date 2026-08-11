-- =====================================================
-- MIGRASI: fitur "Hutang & Piutang"
-- Jalankan ini di phpMyAdmin HANYA JIKA database kamu
-- sudah pernah diimport sebelumnya (sebelum update ini).
-- Kalau baru mau install dari nol, cukup import database.sql
-- yang baru (tabel & kolom ini sudah termasuk di dalamnya).
-- =====================================================

ALTER TABLE transactions
    ADD COLUMN debt_id INT NULL AFTER transfer_id;

CREATE TABLE IF NOT EXISTS debts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('hutang','piutang') NOT NULL,
    person_name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    amount DECIMAL(15,2) NOT NULL,
    account_id INT NULL,
    status ENUM('belum_lunas','lunas') NOT NULL DEFAULT 'belum_lunas',
    tx_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
