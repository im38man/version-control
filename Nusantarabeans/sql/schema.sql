-- Nusantara Beans - Skema Database
CREATE DATABASE IF NOT EXISTS nusantara_beans CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nusantara_beans;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    recovery_email VARCHAR(150) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin_master','admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
