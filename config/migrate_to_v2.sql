-- ============================================================
--  migrate_to_v2.sql (Simple Version)
--  Run in phpMyAdmin → issuetracker database → SQL tab
-- ============================================================

USE issuetracker;

-- ── 1. Add title column to issues ────────────────────────────
ALTER TABLE issues ADD COLUMN title VARCHAR(255) NULL;

-- ── 2. Add story_points column to issues ──────────────────────
ALTER TABLE issues ADD COLUMN story_points ENUM('1','2','3','5','8','13') NULL;

-- ── 3. Add area_path column to issues ─────────────────────────
ALTER TABLE issues ADD COLUMN area_path VARCHAR(255) NULL;

-- ── 4. Add iteration_path column to issues ───────────────────
ALTER TABLE issues ADD COLUMN iteration_path VARCHAR(255) NULL;

-- ── 5. Add acceptance_criteria column to issues ──────────────
ALTER TABLE issues ADD COLUMN acceptance_criteria TEXT NULL;

-- ── 6. Add status column to issues ────────────────────────────
ALTER TABLE issues ADD COLUMN status ENUM('In Progress','Fixed','Resolved') NULL;

-- ── 7. Add author column to comments ──────────────────────────
ALTER TABLE comments ADD COLUMN author VARCHAR(120) NULL;

-- ── 8. Create particulars table ────────────────────────────────
CREATE TABLE IF NOT EXISTS particulars (
    particular_id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    isActive TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (particular_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 9. Seed particulars ───────────────────────────────────────
INSERT INTO particulars (name, isActive) VALUES
    ('MCC', 1), ('SEC', 1), ('MSP', 1), ('BPI', 1),
    ('TBG', 1), ('RCBC', 1), ('WPY', 1), ('Inventory', 1), ('Helpdesk', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ── 10. Add particular_id to users ────────────────────────────
ALTER TABLE users ADD COLUMN particular_id INT NULL;

-- ── 11. Verify ────────────────────────────────────────────────
SELECT 'DONE - Check results below:' AS message;
DESCRIBE issues;
DESCRIBE comments;
SELECT * FROM particulars;
SELECT id, name, role, particular_id FROM users LIMIT 5;

