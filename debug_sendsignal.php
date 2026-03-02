<?php
session_start();
include('assets/inc/config.php');

// Get today's date
$rdate = date('Y-m-d');

// Get user's campus
$campus_id = isset($_SESSION['working_location_id']) ? (int)$_SESSION['working_location_id'] : null;
$doc_id = isset($_SESSION['doc_id']) ? $_SESSION['doc_id'] : 'NOT SET';
$doc_number = isset($_SESSION['doc_number']) ? $_SESSION['doc_number'] : 'NOT SET';

echo "<h2>Debug: Today's Patients Query Check</h2>";
echo "<p>Date: $rdate</p>";
echo "<p>User Campus ID: " . ($campus_id ? $campus_id : 'NOT SET') . "</p>";
echo "<p>Doc ID: $doc_id</p>";
echo "<p>Doc Number: $doc_number</p>";

// Check if campus_id column exists
$hascamp = 0;
$resCol = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id'");
if ($resCol) {
    $rowCol = $resCol->fetch_assoc();
    $hascamp = isset($rowCol['cnt']) ? (int)$rowCol['cnt'] : 0;
}

echo "<h3>Campus Column Check</h3>";
echo "<p>campus_id column exists: " . ($hascamp ? "YES" : "NO") . "</p>";

// Query all sendsignal records for today
echo "<h3>All sendsignal records for today</h3>";
$query_all = "SELECT id, pat_code, Fullname, Date, status, campus_id FROM sendsignal WHERE Date = ? ORDER BY id DESC";
$stmt_all = $mysqli->prepare($query_all);
$stmt_all->bind_param('s', $rdate);
$stmt_all->execute();
$result_all = $stmt_all->get_result();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Patient Code</th><th>Fullname</th><th>Date</th><th>Status</th><th>Campus ID</th></tr>";
while ($row = $result_all->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['pat_code'] . "</td>";
    echo "<td>" . $row['Fullname'] . "</td>";
    echo "<td>" . $row['Date'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . ($row['campus_id'] ? $row['campus_id'] : 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Query filtered by user's campus
if ($hascamp && $campus_id) {
    echo "<h3>Records for user's campus (campus_id = $campus_id) with status='Not Yet'</h3>";
    $query_filtered = "SELECT id, pat_code, Fullname, Date, status, campus_id FROM sendsignal WHERE Date = ? AND status = 'Not Yet' AND campus_id = ? ORDER BY id DESC";
    $stmt_filtered = $mysqli->prepare($query_filtered);
    $stmt_filtered->bind_param('si', $rdate, $campus_id);
    $stmt_filtered->execute();
    $result_filtered = $stmt_filtered->get_result();
    
    echo "<p>Found " . $result_filtered->num_rows . " records</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Patient Code</th><th>Fullname</th><th>Date</th><th>Status</th><th>Campus ID</th></tr>";
    while ($row = $result_filtered->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['pat_code'] . "</td>";
        echo "<td>" . $row['Fullname'] . "</td>";
        echo "<td>" . $row['Date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . ($row['campus_id'] ? $row['campus_id'] : 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<h3>No campus filtering (campus column may not exist or user has no campus)</h3>";
    $query_no_filter = "SELECT id, pat_code, Fullname, Date, status, campus_id FROM sendsignal WHERE Date = ? AND status = 'Not Yet' ORDER BY id DESC";
    $stmt_no_filter = $mysqli->prepare($query_no_filter);
    $stmt_no_filter->bind_param('s', $rdate);
    $stmt_no_filter->execute();
    $result_no_filter = $stmt_no_filter->get_result();
    
    echo "<p>Found " . $result_no_filter->num_rows . " records</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Patient Code</th><th>Fullname</th><th>Date</th><th>Status</th><th>Campus ID</th></tr>";
    while ($row = $result_no_filter->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['pat_code'] . "</td>";
        echo "<td>" . $row['Fullname'] . "</td>";
        echo "<td>" . $row['Date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . ($row['campus_id'] ? $row['campus_id'] : 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='his_admin_todays_visitn.php'>Back to Today's Patient Visit</a></p>";
?>
