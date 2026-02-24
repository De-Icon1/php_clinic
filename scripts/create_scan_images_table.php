<?php
// scripts/create_scan_images_table.php
require_once __DIR__ . '/../assets/inc/config.php';

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    echo "Error: MySQLi connection not found.\n";
    exit(1);
}

$sql = "CREATE TABLE IF NOT EXISTS `scan_images` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `scan_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_scan` (`scan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql) === TRUE) {
    echo "scan_images table created or already exists.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}
