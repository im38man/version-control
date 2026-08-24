-- ==========================================================
-- Migration v2: Trailing Stop Loss per level Risk:Reward (SL1-SL5)
-- Jalankan ini di phpMyAdmin > database kamu > tab SQL > Go.
-- Aman walau kolom sl1..sl5 udah ada (pakai IF NOT EXISTS).
-- ==========================================================

-- 1) Kalau kamu SUDAH pernah jalanin migration_add_rr_columns.sql sebelumnya
--    (yang bikin kolom sl_hit, sl_pips, sl_usd tunggal), hapus dulu kolom lama itu.
--    Kalau belum pernah, baris ini aman diabaikan / akan error "unknown column" —
--    tinggal skip 3 baris DROP di bawah kalau errornya begitu.
ALTER TABLE trades
  DROP COLUMN IF EXISTS sl_hit,
  DROP COLUMN IF EXISTS sl_pips,
  DROP COLUMN IF EXISTS sl_usd;

-- 2) Tambah kolom trailing SL per level (SL1-SL5)
ALTER TABLE trades
  ADD COLUMN IF NOT EXISTS sl1_hit TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS sl2_hit TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS sl3_hit TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS sl4_hit TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS sl5_hit TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS sl1_price DECIMAL(18,5) NULL,
  ADD COLUMN IF NOT EXISTS sl2_price DECIMAL(18,5) NULL,
  ADD COLUMN IF NOT EXISTS sl3_price DECIMAL(18,5) NULL,
  ADD COLUMN IF NOT EXISTS sl4_price DECIMAL(18,5) NULL,
  ADD COLUMN IF NOT EXISTS sl5_price DECIMAL(18,5) NULL,
  ADD COLUMN IF NOT EXISTS sl1_pips DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS sl1_usd DECIMAL(14,2) NULL,
  ADD COLUMN IF NOT EXISTS sl2_pips DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS sl2_usd DECIMAL(14,2) NULL,
  ADD COLUMN IF NOT EXISTS sl3_pips DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS sl3_usd DECIMAL(14,2) NULL,
  ADD COLUMN IF NOT EXISTS sl4_pips DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS sl4_usd DECIMAL(14,2) NULL,
  ADD COLUMN IF NOT EXISTS sl5_pips DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS sl5_usd DECIMAL(14,2) NULL;

-- Cek hasilnya:
-- DESCRIBE trades;
