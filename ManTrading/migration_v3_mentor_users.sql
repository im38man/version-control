-- ==========================================================
-- Migration v3: Kelola User + Pengajuan Mentor + Posting Community oleh Mentor
-- Jalankan ini di phpMyAdmin > database kamu > tab SQL > Go.
-- Aman dijalankan berkali-kali (pakai IF NOT EXISTS).
-- ==========================================================

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS mentor_status ENUM('none','pending','approved','rejected') NOT NULL DEFAULT 'none' AFTER vip_status;

CREATE TABLE IF NOT EXISTS mentor_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  message VARCHAR(500) NULL,
  admin_note VARCHAR(500) NULL,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  responded_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Cek hasilnya:
-- DESCRIBE users;
-- DESCRIBE mentor_requests;
