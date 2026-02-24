<?php
// scripts/create_scan_table.php
// Run this in browser (http://localhost/clinic/scripts/create_scan_table.php)
// or CLI: php scripts/create_scan_table.php

// Adjust path if your setup differs
require_once __DIR__ . '/../assets/inc/config.php';

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    echo "\nError: MySQLi connection \$mysqli not found. Check assets/inc/config.php\n";
    exit(1);
}

$createSql = "CREATE TABLE IF NOT EXISTS `scan` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10,2) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($mysqli->query($createSql) === TRUE) {
    echo "Scan table checked/created successfully.\n";
    // Optional: show existing rows count
    $res = $mysqli->query("SELECT COUNT(*) AS cnt FROM scan");
    if ($res) {
        $row = $res->fetch_assoc();
        echo "Existing scan rows: " . intval($row['cnt']) . "\n";
    }
    exit(0);
} else {
    echo "Error creating scan table: " . $mysqli->error . "\n";
    exit(2);
}
