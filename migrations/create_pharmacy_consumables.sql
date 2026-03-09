-- Migration: create pharmacy consumables tables
-- Run in phpMyAdmin or MySQL CLI after taking a DB backup

CREATE TABLE IF NOT EXISTS pharmacy_consumables (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pharmacy_consumable_stock (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  consumable_id INT NOT NULL,
  campus_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  supplier_name VARCHAR(255) DEFAULT NULL,
  lpo_ref VARCHAR(100) DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (consumable_id) REFERENCES pharmacy_consumables(id) ON DELETE CASCADE,
  FOREIGN KEY (campus_id) REFERENCES campus_locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: seed with a sample item
INSERT IGNORE INTO pharmacy_consumables (name, category) VALUES ('Paracetamol 500mg', 'Tablet');

-- Optional: create sample stock record for Main Campus if campus exists
INSERT INTO pharmacy_consumable_stock (consumable_id, campus_id, quantity)
SELECT pc.id, cl.id, 100
FROM pharmacy_consumables pc
JOIN campus_locations cl ON cl.name = 'Main Campus'
WHERE pc.name = 'Paracetamol 500mg'
LIMIT 1;
