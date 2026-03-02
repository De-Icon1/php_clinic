<?php
session_start();
include('assets/inc/config.php');

echo "<h1>Server Database Schema Check & Migration</h1>";

// Check if columns exist
echo "<h2>1. Checking Database Schema</h2>";

// Check sendsignal table for campus_id
$check_sendsignal = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id'");
$sendsignal_col = $check_sendsignal->fetch_assoc()['cnt'];

echo "<p><strong>sendsignal.campus_id exists:</strong> " . ($sendsignal_col ? "✅ YES" : "❌ NO") . "</p>";

// Check his_docs table for campus_id
$check_his_docs = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='his_docs' AND COLUMN_NAME='campus_id'");
$his_docs_col = $check_his_docs->fetch_assoc()['cnt'];

echo "<p><strong>his_docs.campus_id exists:</strong> " . ($his_docs_col ? "✅ YES" : "❌ NO") . "</p>";

// Check campus_locations table
$check_campus_table = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='campus_locations'");
$campus_table = $check_campus_table->fetch_assoc()['cnt'];

echo "<p><strong>campus_locations table exists:</strong> " . ($campus_table ? "✅ YES" : "❌ NO") . "</p>";

// Check session variables
echo "<h2>2. Checking Session Variables (after login)</h2>";
echo "<p><strong>\$_SESSION['doc_id']:</strong> " . (isset($_SESSION['doc_id']) ? $_SESSION['doc_id'] : "NOT SET") . "</p>";
echo "<p><strong>\$_SESSION['doc_number']:</strong> " . (isset($_SESSION['doc_number']) ? $_SESSION['doc_number'] : "NOT SET") . "</p>";
echo "<p><strong>\$_SESSION['working_location_id']:</strong> " . (isset($_SESSION['working_location_id']) ? $_SESSION['working_location_id'] : "NOT SET") . "</p>";
echo "<p><strong>\$_SESSION['campus_id']:</strong> " . (isset($_SESSION['campus_id']) ? $_SESSION['campus_id'] : "NOT SET") . "</p>";

// Check his_docs for any records with campus_id
echo "<h2>3. Sample Data Check</h2>";
if ($his_docs_col) {
    $sample = $mysqli->query("SELECT doc_id, doc_number, doc_name, campus_id FROM his_docs LIMIT 5");
    echo "<p><strong>Sample his_docs records:</strong></p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Doc ID</th><th>Doc Number</th><th>Doc Name</th><th>Campus ID</th></tr>";
    while ($row = $sample->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['doc_id'] . "</td>";
        echo "<td>" . $row['doc_number'] . "</td>";
        echo "<td>" . $row['doc_name'] . "</td>";
        echo "<td>" . ($row['campus_id'] ? $row['campus_id'] : "NULL") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Cannot check - his_docs.campus_id column doesn't exist yet</p>";
}

// Check sendsignal for any records
echo "<p><strong>Sample sendsignal records (today):</strong></p>";
$today = date('Y-m-d');
$sendsig_check = $mysqli->query("SELECT id, pat_code, Fullname, Date, status " . ($sendsignal_col ? ", campus_id" : "") . " FROM sendsignal WHERE Date='$today' LIMIT 5");
if ($sendsig_check->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Patient Code</th><th>Fullname</th><th>Date</th><th>Status</th>" . ($sendsignal_col ? "<th>Campus ID</th>" : "") . "</tr>";
    while ($row = $sendsig_check->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['pat_code'] . "</td>";
        echo "<td>" . $row['Fullname'] . "</td>";
        echo "<td>" . $row['Date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        if ($sendsignal_col) echo "<td>" . ($row['campus_id'] ? $row['campus_id'] : "NULL") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No sendsignal records found for today</p>";
}

echo "<h2>4. Required Migrations</h2>";

if (!$sendsignal_col) {
    echo "<p><strong>❌ REQUIRED: Add campus_id to sendsignal table</strong></p>";
    echo "<pre>ALTER TABLE sendsignal ADD COLUMN campus_id INT NULL DEFAULT NULL AFTER id;</pre>";
}

if (!$his_docs_col) {
    echo "<p><strong>❌ REQUIRED: Add campus_id to his_docs table</strong></p>";
    echo "<pre>ALTER TABLE his_docs ADD COLUMN campus_id INT NULL;</pre>";
}

if (!$campus_table) {
    echo "<p><strong>❌ REQUIRED: Create campus_locations table</strong></p>";
    echo "<pre>
CREATE TABLE campus_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    location_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
    </pre>";
}

echo "<h2>5. Recommended Actions</h2>";
echo "<ol>";
echo "<li>Run the required migrations above in phpMyAdmin or MySQL CLI</li>";
echo "<li>Create campus locations: INSERT INTO campus_locations (name, location_name) VALUES ('Main Campus', 'Main Location'), ('Mini Campus', 'Mini Location');</li>";
echo "<li>Assign staff to campuses: UPDATE his_docs SET campus_id = 1 WHERE doc_id IN (1, 2, 3); -- adjust as needed</li>";
echo "<li>Test login to verify \$_SESSION['working_location_id'] is set</li>";
echo "<li>Send patient signal and verify in nursing queue</li>";
echo "</ol>";

?>
