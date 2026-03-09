-- ============================================================
--  master_users.sql
--  Combined Users + Particulars Master File
--  Run this in phpMyAdmin under the issuetracker database
-- ============================================================

USE issuetracker;

-- ── Step 1: Create particulars table (if not exists) ───────────────
CREATE TABLE IF NOT EXISTS particulars (
    particular_id INT          NOT NULL AUTO_INCREMENT,
    name         VARCHAR(255) NOT NULL,
    isActive     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (particular_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Seed some starter particulars ─────────────────────────────────
INSERT INTO particulars (name, isActive) VALUES
    ('MCC', 1),
    ('SEC', 1),
    ('Inventory', 1),
    ('Helpdesk', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ── Step 2: Add particular_id column to users table ───────────────
ALTER TABLE users 
ADD COLUMN particular_id INT NULL;

-- ── Add foreign key ────────────────────────────────────────────────
ALTER TABLE users
ADD FOREIGN KEY (particular_id) REFERENCES particulars(particular_id) ON DELETE SET NULL;

-- ── Step 3: Auto-link existing users to particulars based on name ───
UPDATE users u
LEFT JOIN particulars p ON LOWER(u.name) LIKE CONCAT('%', LOWER(p.name), '%')
SET u.particular_id = p.particular_id
WHERE u.particular_id IS NULL;

-- ── Step 4: Seed sample users with particulars ─────────────────────
INSERT INTO users (name, email, role, particular_id) VALUES
    ('Admin User',  'admin@issuetracker.local',  'admin', NULL),
    ('MCC Staff',   'mcc@issuetracker.local',   'reporter', (SELECT particular_id FROM particulars WHERE name = 'MCC')),
    ('SEC Staff',   'sec@issuetracker.local',    'reporter', (SELECT particular_id FROM particulars WHERE name = 'SEC')),
    ('Inventory',   'inventory@issuetracker.local', 'reporter', (SELECT particular_id FROM particulars WHERE name = 'Inventory')),
    ('Helpdesk',    'helpdesk@issuetracker.local',  'reporter', (SELECT particular_id FROM particulars WHERE name = 'Helpdesk')),
    ('Developer',   'dev@issuetracker.local',    'developer', NULL)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ── Step 5: Create VIEW for master users ──────────────────────────
CREATE OR REPLACE VIEW v_master_users AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.role,
    u.isActive,
    u.created_at,
    u.updated_at,
    p.particular_id,
    p.name AS particular_name
FROM users u
LEFT JOIN particulars p ON u.particular_id = p.particular_id;

-- ── Verify ─────────────────────────────────────────────────────────
SELECT * FROM v_master_users;
SELECT * FROM particulars;

