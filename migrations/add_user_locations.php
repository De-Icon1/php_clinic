<?php
// One-time helper to ensure every user in his_docs has a location (campus_id)
// Run from CLI or browser: php migrations/add_user_locations.php

require_once __DIR__ . '/../assets/inc/config.php';

function ensure_main_campus($mysqli) {
    // Try to find an existing "Main Campus Health Centre" entry
    $name = 'Main Campus Health Centre';
    $stmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
    if (!$stmt) {
        die('Prepare failed for campus_locations: ' . $mysqli->error);
    }
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $stmt->close();
        return (int)$row['id'];
    }
    $stmt->close();

    // If not found, insert a default main campus location
    $ins = $mysqli->prepare("INSERT INTO campus_locations (name) VALUES (?)");
    if (!$ins) {
        die('Prepare failed for insert campus_locations: ' . $mysqli->error);
    }
    $ins->bind_param('s', $name);
    if (!$ins->execute()) {
        die('Insert failed for campus_locations: ' . $mysqli->error);
    }
    $id = (int)$ins->insert_id;
    $ins->close();
    echo "Created default campus location: {$name} (id={$id})\n";
    return $id;
}

function ensure_his_docs_campus_column($mysqli) {
    $col = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='his_docs' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
    if ($col) {
        echo "Column his_docs.campus_id already exists\n";
        return;
    }

    $sql = "ALTER TABLE his_docs ADD COLUMN campus_id INT NULL";
    if ($mysqli->query($sql)) {
        echo "Added campus_id column to his_docs\n";
    } else {
        die("Failed to add campus_id to his_docs: " . $mysqli->error . "\n");
    }
}

function assign_default_campus_to_users($mysqli, $defaultCampusId) {
    // Only touch users that do not yet have a campus_id set
    $stmt = $mysqli->prepare("UPDATE his_docs SET campus_id = ? WHERE (campus_id IS NULL OR campus_id = 0)");
    if (!$stmt) {
        die('Prepare failed for updating his_docs.campus_id: ' . $mysqli->error);
    }
    $stmt->bind_param('i', $defaultCampusId);
    if ($stmt->execute()) {
        echo "Assigned campus_id={$defaultCampusId} to " . $stmt->affected_rows . " user(s) with no location set\n";
    } else {
        die('Failed to assign campus_id to users: ' . $mysqli->error . "\n");
    }
    $stmt->close();
}

// --- Main runner ---

echo "Starting add_user_locations migration...\n";

// 1) Ensure his_docs has campus_id column
ensure_his_docs_campus_column($mysqli);

// 2) Ensure there is at least one main campus entry and get its id
$mainCampusId = ensure_main_campus($mysqli);

echo "Using default campus_id={$mainCampusId} for users without a location.\n";

// 3) Assign this campus_id to all existing users that have no location yet
assign_default_campus_to_users($mysqli, $mainCampusId);

echo "Done. You can now edit his_docs.campus_id per user (via DB or an admin UI)\n";

?>
