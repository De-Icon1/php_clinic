<?php
// Migration: add campus/location columns to pharmacy tables and backfill campus_id
session_start();
include_once(__DIR__ . '/../assets/inc/config.php');

function column_exists($mysqli, $table, $column) {
    $t = $mysqli->real_escape_string($table);
    $c = $mysqli->real_escape_string($column);
    $q = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$t}' AND COLUMN_NAME = '{$c}'";
    $r = $mysqli->query($q);
    if (!$r) return false;
    $row = $r->fetch_assoc();
    return ((int)$row['cnt']) > 0;
}

function ensure_main_campus_id($mysqli) {
    $name = 'Main Campus Health Centre';
    $stmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $stmt->close();
            return (int)$row['id'];
        }
        $stmt->close();
    }
    $ins = $mysqli->prepare("INSERT INTO campus_locations (name) VALUES (?)");
    $ins->bind_param('s', $name);
    $ins->execute();
    $id = $ins->insert_id;
    $ins->close();
    return (int)$id;
}

$messages = [];

// 1. Ensure pharmacy.campus_id exists
if (!column_exists($mysqli, 'pharmacy', 'campus_id')) {
    if ($mysqli->query("ALTER TABLE pharmacy ADD COLUMN campus_id INT(11) DEFAULT NULL")) {
        $messages[] = 'Added pharmacy.campus_id column.';
    } else {
        $messages[] = 'Failed to add pharmacy.campus_id: ' . $mysqli->error;
    }
} else {
    $messages[] = 'pharmacy.campus_id already exists.';
}

// 2. Ensure pharmacy_order.campus_id and pharmacy_order.pharmacy_location_id exist
if (!column_exists($mysqli, 'pharmacy_order', 'campus_id')) {
    if ($mysqli->query("ALTER TABLE pharmacy_order ADD COLUMN campus_id INT(11) DEFAULT NULL")) {
        $messages[] = 'Added pharmacy_order.campus_id column.';
    } else {
        $messages[] = 'Failed to add pharmacy_order.campus_id: ' . $mysqli->error;
    }
} else {
    $messages[] = 'pharmacy_order.campus_id already exists.';
}

if (!column_exists($mysqli, 'pharmacy_order', 'pharmacy_location_id')) {
    if ($mysqli->query("ALTER TABLE pharmacy_order ADD COLUMN pharmacy_location_id INT(11) DEFAULT NULL")) {
        $messages[] = 'Added pharmacy_order.pharmacy_location_id column.';
    } else {
        $messages[] = 'Failed to add pharmacy_order.pharmacy_location_id: ' . $mysqli->error;
    }
} else {
    $messages[] = 'pharmacy_order.pharmacy_location_id already exists.';
}

// 3. Ensure pharmacy_stock.campus_id exists
if (!column_exists($mysqli, 'pharmacy_stock', 'campus_id')) {
    if ($mysqli->query("ALTER TABLE pharmacy_stock ADD COLUMN campus_id INT(11) DEFAULT NULL")) {
        $messages[] = 'Added pharmacy_stock.campus_id column.';
    } else {
        $messages[] = 'Failed to add pharmacy_stock.campus_id: ' . $mysqli->error;
    }
} else {
    $messages[] = 'pharmacy_stock.campus_id already exists.';
}

// 4. Backfill campus_id with Main Campus for existing rows
$mainCampusId = ensure_main_campus_id($mysqli);

$tablesToBackfill = ['pharmacy', 'pharmacy_order', 'pharmacy_stock'];
foreach ($tablesToBackfill as $tbl) {
    if (column_exists($mysqli, $tbl, 'campus_id')) {
        $sql = "UPDATE {$tbl} SET campus_id = {$mainCampusId} WHERE campus_id IS NULL OR campus_id = 0";
        if ($mysqli->query($sql)) {
            $messages[] = "Backfilled {$tbl}.campus_id with Main Campus for NULL/0 rows.";
        } else {
            $messages[] = "Failed to backfill {$tbl}.campus_id: " . $mysqli->error;
        }
    }
}

?><!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Pharmacy Location Migration</title></head>
<body>
<h2>Pharmacy Location Migration</h2>
<ul>
<?php foreach ($messages as $m): ?>
    <li><?php echo htmlspecialchars($m); ?></li>
<?php endforeach; ?>
</ul>
<p>Done. You can close this window.</p>
</body>
</html>
