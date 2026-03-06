-- ============================================================
--  update_users.sql
--  Run in phpMyAdmin → issuetracker DB → SQL tab
--
--  1. Sets username = firstname@issuetracker for all users
--  2. Sets MD5 password = firstname@issuetracker
--  3. Updates roles:
--       Stephen, Mark → developer
--       Dianne, Kath, Jemson, Josh → reporter (tester/QA)
--       Admin User → admin
-- ============================================================

USE issuetracker;

-- ── STEP 1: Add username/password columns if not yet added ──
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS username VARCHAR(100) NULL UNIQUE AFTER name,
  ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL AFTER username;

-- ── STEP 2: Update each user ─────────────────────────────────

-- Stephen (developer)
-- password: stephen@issuetracker → MD5: d4e8833571e13944e2d2e60aa52c8975
UPDATE users SET
  username = 'stephen@issuetracker',
  password = 'd4e8833571e13944e2d2e60aa52c8975',
  role     = 'developer'
WHERE id = 1;

-- Mark Anthony Ong (developer)
-- password: mark@issuetracker → MD5: 0db28adf711da73abd8efae8d7c09f7f
UPDATE users SET
  username = 'mark@issuetracker',
  password = '0db28adf711da73abd8efae8d7c09f7f',
  role     = 'developer'
WHERE id = 2;

-- Dianne (reporter/tester)
-- password: dianne@issuetracker → MD5: 6c09bb82b2eb4899c5c9e6c0bfd33eba
UPDATE users SET
  username = 'dianne@issuetracker',
  password = '6c09bb82b2eb4899c5c9e6c0bfd33eba',
  role     = 'reporter'
WHERE id = 3;

-- Kath (reporter/tester)
-- password: kath@issuetracker → MD5: bb747e9d755799c2a22542bd3f3434df
UPDATE users SET
  username = 'kath@issuetracker',
  password = 'bb747e9d755799c2a22542bd3f3434df',
  role     = 'reporter'
WHERE id = 4;

-- Jemson (reporter/tester)
-- password: jemson@issuetracker → MD5: e64cb9dde759d775d7ca5cf53329a34b
UPDATE users SET
  username = 'jemson@issuetracker',
  password = 'e64cb9dde759d775d7ca5cf53329a34b',
  role     = 'reporter'
WHERE id = 5;

-- Josh (reporter/tester)
-- password: josh@issuetracker → MD5: 4a979e09220070842a5c39f67fb339c2
UPDATE users SET
  username = 'josh@issuetracker',
  password = '4a979e09220070842a5c39f67fb339c2',
  role     = 'reporter'
WHERE id = 6;

-- Admin User (admin)
-- password: admin@issuetracker → MD5: ec1ffa21083a59fedfd226b915cf8429
UPDATE users SET
  username = 'admin@issuetracker',
  password = 'ec1ffa21083a59fedfd226b915cf8429',
  role     = 'admin'
WHERE id = 7;

-- ── STEP 3: Verify all users ─────────────────────────────────
SELECT id, name, username, role,
       IF(password IS NOT NULL, 'SET ✓', 'NOT SET ✗') AS password_status
FROM users
ORDER BY id;
