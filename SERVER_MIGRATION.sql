-- Server Database Migration: Add Campus Filtering Support
-- Run this on the Plesk server database
-- Last Updated: March 2, 2026

-- Step 1: Create campus_locations table if it doesn't exist
CREATE TABLE IF NOT EXISTS campus_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    location_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Add campus_id to his_docs table if column doesn't exist
ALTER TABLE his_docs ADD COLUMN campus_id INT NULL;

-- Step 3: Add campus_id to sendsignal table if column doesn't exist
ALTER TABLE sendsignal ADD COLUMN campus_id INT NULL DEFAULT NULL AFTER id;

-- Step 4: Insert default campus locations (CUSTOMIZE THESE FOR YOUR SETUP)
INSERT IGNORE INTO campus_locations (name, location_name) VALUES
('1', 'Main Campus'),
('2', 'Mini Campus');

-- Step 5: IMPORTANT - Assign staff to campuses
-- CUSTOMIZE THIS BASED ON YOUR ACTUAL STAFF AND CAMPUSES
-- Uncomment and modify the lines below:
-- UPDATE his_docs SET campus_id = 1 WHERE doc_id IN (list of main campus doctor IDs);
-- UPDATE his_docs SET campus_id = 2 WHERE doc_id IN (list of mini campus doctor IDs);

-- Verify the migrations worked
SELECT 'his_docs columns:' as `Check`;
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='his_docs' AND COLUMN_NAME='campus_id';

SELECT 'sendsignal columns:' as `Check`;
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id';

SELECT 'campus_locations table:' as `Check`;
SELECT * FROM campus_locations;

SELECT 'his_docs staff assignments:' as `Check`;
SELECT doc_id, doc_number, doc_name, campus_id FROM his_docs LIMIT 10;
