-- Run this SQL script to set up the database
-- You can run it via phpMyAdmin or MySQL CLI: mysql -u root -p < setup.sql

CREATE DATABASE IF NOT EXISTS phplogin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE phplogin;

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login  TIMESTAMP NULL,
    is_active   TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: add an index on email for faster lookups
CREATE INDEX idx_email ON users(email);
