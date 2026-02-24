<?php
// Run this script from browser or CLI to add campus_id column and assign Doctor/Nursing/Laboratory locations
// Usage: php migrations/assign_service_locations.php

require_once __DIR__ . '/../assets/inc/config.php';

function run($mysqli) {
    // Ensure campus locations exist
    $names = ['Doctor','Nursing','Laboratory'];
    foreach ($names as $n) {
        $stmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
        $stmt->bind_param('s', $n);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $ins = $mysqli->prepare("INSERT INTO campus_locations (name) VALUES (?)");
            $ins->bind_param('s', $n);
            $ins->execute();
            echo "Inserted campus location: $n\n";
        } else {
            echo "Campus exists: $n\n";
        }
    }

    // Add campus_id column to his_docs if missing
    $col = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='his_docs' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
    if (!$col) {
        $alter = "ALTER TABLE his_docs ADD COLUMN campus_id INT NULL";
        if ($mysqli->query($alter)) {
            echo "Added campus_id column to his_docs\n";
        } else {
            echo "Failed to add campus_id: " . $mysqli->error . "\n";
        }
    } else {
        echo "his_docs.campus_id already exists\n";
    }

    // Map doc_dept values to campus ids and update his_docs
    $map = [
        'Doctor' => 'Doctor',
        'Nursing' => 'Nursing',
        'Laboratory' => 'Laboratory'
    ];

    foreach ($map as $dept => $campusName) {
        $s = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
        $s->bind_param('s', $campusName);
        $s->execute();
        $r = $s->get_result();
        if ($row = $r->fetch_assoc()) {
            $campusId = intval($row['id']);
            $u = $mysqli->prepare("UPDATE his_docs SET campus_id = ? WHERE doc_dept = ? AND (campus_id IS NULL OR campus_id = 0)");
            $u->bind_param('is', $campusId, $dept);
            if ($u->execute()) {
                echo "Assigned campus_id={$campusId} to users in dept {$dept}\n";
            } else {
                echo "Failed to assign for {$dept}: " . $mysqli->error . "\n";
            }
        } else {
            echo "Campus not found: {$campusName}\n";
        }
    }

    echo "Done.\n";
}

run($mysqli);

?>