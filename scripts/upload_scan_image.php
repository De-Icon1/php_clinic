<?php
// scripts/upload_scan_image.php
session_start();
require_once __DIR__ . '/../assets/inc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$scan_id = isset($_POST['scan_id']) ? (int) $_POST['scan_id'] : 0;
$department = isset($_POST['department']) ? trim($_POST['department']) : null;
$patient_code = isset($_POST['patient_code']) ? trim($_POST['patient_code']) : null;
$test_name = isset($_POST['test_name']) ? trim($_POST['test_name']) : null;

if (!$scan_id || !isset($_FILES['scan_image'])) {
    $_SESSION['err'] = 'Please select a scan and an image file.';
    // try to redirect back to patient result if available
    if ($patient_code && $test_name) {
        header('Location: ../scan_result.php?pat_number=' . urlencode($patient_code) . '&test=' . urlencode($test_name));
    } else {
        header('Location: ../setup_radiology.php');
    }
    exit;
}

$upload = $_FILES['scan_image'];
$allowed = ['image/jpeg','image/png','image/gif','application/pdf'];
if (!in_array($upload['type'], $allowed)) {
    $_SESSION['err'] = 'Unsupported file type. Use JPG, PNG, GIF or PDF.';
    if ($patient_code && $test_name) {
        header('Location: ../scan_result.php?pat_number=' . urlencode($patient_code) . '&test=' . urlencode($test_name));
    } else {
        header('Location: ../setup_radiology.php');
    }
    exit;
}

$uploadDir = __DIR__ . '/../uploads/scans/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = pathinfo($upload['name'], PATHINFO_EXTENSION);
$fname = 'scan_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$target = $uploadDir . $fname;

if (!move_uploaded_file($upload['tmp_name'], $target)) {
    $_SESSION['err'] = 'Failed to move uploaded file.';
    if ($patient_code && $test_name) {
        header('Location: ../scan_result.php?pat_number=' . urlencode($patient_code) . '&test=' . urlencode($test_name));
    } else {
        header('Location: ../setup_radiology.php');
    }
    exit;
}

// store relative path
$relPath = 'uploads/scans/' . $fname;
$uploader = isset($_SESSION['doc_id']) ? (int) $_SESSION['doc_id'] : null;

// Detect if scan_images has columns patient_code/test_name
$colsRes = $mysqli->query("SHOW COLUMNS FROM scan_images LIKE 'patient_code'");
$hasPatient = ($colsRes && $colsRes->num_rows > 0);
$colsRes2 = $mysqli->query("SHOW COLUMNS FROM scan_images LIKE 'test_name'");
$hasTestName = ($colsRes2 && $colsRes2->num_rows > 0);

if ($hasPatient && $hasTestName) {
    $stmt = $mysqli->prepare("INSERT INTO scan_images (scan_id, file_path, department, uploaded_by, patient_code, test_name) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('ississ', $scan_id, $relPath, $department, $uploader, $patient_code, $test_name);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success'] = 'Image uploaded successfully.';
    } else {
        $_SESSION['err'] = 'Database error: ' . $mysqli->error;
        @unlink($target);
    }
} else {
    // fallback to older schema
    $stmt = $mysqli->prepare("INSERT INTO scan_images (scan_id, file_path, department, uploaded_by) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('issi', $scan_id, $relPath, $department, $uploader);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success'] = 'Image uploaded successfully.';
    } else {
        $_SESSION['err'] = 'Database error: ' . $mysqli->error;
        @unlink($target);
    }
}

// Redirect back to patient result page if context provided
if ($patient_code && $test_name) {
    header('Location: ../scan_result.php?pat_number=' . urlencode($patient_code) . '&test=' . urlencode($test_name));
} else {
    header('Location: ../setup_radiology.php');
}
exit;
