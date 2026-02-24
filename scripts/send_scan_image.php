<?php
// scripts/send_scan_image.php
session_start();
require_once __DIR__ . '/../assets/inc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$image_id = isset($_POST['image_id']) ? (int) $_POST['image_id'] : 0;
$department = isset($_POST['department']) ? trim($_POST['department']) : null;

if (!$image_id || !$department) {
    $_SESSION['err'] = 'Invalid request.';
    header('Location: ../setup_radiology.php');
    exit;
}

$stmt = $mysqli->prepare("UPDATE scan_images SET department = ? WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('si', $department, $image_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success'] = 'Image assigned to department: ' . htmlspecialchars($department);
} else {
    $_SESSION['err'] = 'Database error: ' . $mysqli->error;
}

header('Location: ../setup_radiology.php');
exit;
