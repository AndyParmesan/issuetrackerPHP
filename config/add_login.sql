-- ============================================================
--  add_login.sql
--  Run in phpMyAdmin → issuetracker DB → SQL tab
--  Adds username and password columns to users table
--  Seeds an admin user with password: admin (MD5 hashed)
-- ============================================================

USE issuetracker;

-- Add username column if not exists
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS username VARCHAR(100) NULL UNIQUE AFTER name,
  ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL AFTER username;

-- ── Seed admin user ─────────────────────────────────────────
-- Password: admin  →  MD5: 21232f297a57a5a743894a0e4a801fc3
INSERT INTO users (name, username, email, password, role)
VALUES ('Admin User', 'admin', 'admin@issuetracker.local', '21232f297a57a5a743894a0e4a801fc3', 'admin')
ON DUPLICATE KEY UPDATE
  password = '21232f297a57a5a743894a0e4a801fc3',
  username = 'admin';

-- ── Verify ──────────────────────────────────────────────────
SELECT id, name, username, role, 
       IF(password IS NOT NULL, 'SET', 'NOT SET') AS password_status
FROM users;
