-- ==========================================================
-- Migration v4: Reply Komentar + Like Komentar (Community)
-- Jalankan ini di phpMyAdmin > database kamu > tab SQL > Go.
-- Aman dijalankan berkali-kali (pakai IF NOT EXISTS).
-- ==========================================================

ALTER TABLE community_comments
  ADD COLUMN IF NOT EXISTS parent_id INT NULL AFTER post_id;

CREATE TABLE IF NOT EXISTS community_comment_likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  comment_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_comment_like (comment_id, user_id),
  FOREIGN KEY (comment_id) REFERENCES community_comments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Cek hasilnya:
-- DESCRIBE community_comments;
-- DESCRIBE community_comment_likes;
