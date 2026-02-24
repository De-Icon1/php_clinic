<?php
// One-time helper to add campus_id/location to his_patients
// Run from CLI or browser: php migrations/add_patient_locations.php

require_once __DIR__ . '/../assets/inc/config.php';

function ensure_main_campus_for_patients(mysqli $mysqli): int {
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
        return (int) $row['id'];
    }
    $stmt->close();

    $ins = $mysqli->prepare("INSERT INTO campus_locations (name) VALUES (?)");
    if (!$ins) {
        die('Prepare failed for insert campus_locations: ' . $mysqli->error);
    }
    $ins->bind_param('s', $name);
    if (!$ins->execute()) {
        die('Insert failed for campus_locations: ' . $mysqli->error);
    }
    $id = (int) $ins->insert_id;
    $ins->close();
    echo "Created default campus location for patients: {$name} (id={$id})\n";
    return $id;
}

function ensure_his_patients_campus_column(mysqli $mysqli): void {
    $row = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='his_patients' AND COLUMN_NAME='campus_id'");
    $col = $row ? ((int) $row->fetch_assoc()['cnt']) : 0;
    if ($col) {
        echo "Column his_patients.campus_id already exists\n";
        return;
    }

    $sql = "ALTER TABLE his_patients ADD COLUMN campus_id INT NULL";
    if ($mysqli->query($sql)) {
        echo "Added campus_id column to his_patients\n";
    } else {
        die("Failed to add campus_id to his_patients: " . $mysqli->error . "\n");
    }
}

function assign_default_campus_to_patients(mysqli $mysqli, int $defaultCampusId): void {
    $stmt = $mysqli->prepare("UPDATE his_patients SET campus_id = ? WHERE (campus_id IS NULL OR campus_id = 0)");
    if (!$stmt) {
        die('Prepare failed for updating his_patients.campus_id: ' . $mysqli->error);
    }
    $stmt->bind_param('i', $defaultCampusId);
    if ($stmt->execute()) {
        echo "Assigned campus_id={$defaultCampusId} to " . $stmt->affected_rows . " patient record(s) with no location set\n";
    } else {
        die('Failed to assign campus_id to patients: ' . $mysqli->error . "\n");
    }
    $stmt->close();
}

// --- Main ---

echo "Starting add_patient_locations migration...\n";

ensure_his_patients_campus_column($mysqli);
$mainCampusId = ensure_main_campus_for_patients($mysqli);

echo "Using default campus_id={$mainCampusId} for patients without a location.\n";
assign_default_campus_to_patients($mysqli, $mainCampusId);

echo "Done. You can now update his_patients.campus_id per patient for multi-campus setups.\n";

?>
