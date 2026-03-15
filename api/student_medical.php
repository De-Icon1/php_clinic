<?php
// API: Fetch student medical record / medical number from med_data table by regnum.
// URL example:
//   http://your-host/clinic/api/student_medical.php?regnum=35307439DE

require_once __DIR__ . '/config.php';

$regnum = isset($_GET['regnum']) ? trim($_GET['regnum']) : '';
if ($regnum === '' && isset($_POST['regnum'])) {
    $regnum = trim($_POST['regnum']);
}

if ($regnum === '') {
    api_send_json(array(
        'success' => false,
        'error'   => 'Missing regnum parameter',
    ), 400);
}

// Connect to medical DB that holds med_data (oict_mission)
$mysqli = api_get_connection($API_MEDICAL_DB);

$sql = "SELECT regnum, med_id, tribe, medstat, clinic_dt, created_at, updated_at
        FROM med_data
        WHERE regnum = ?
        LIMIT 1";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    api_send_json(array(
        'success' => false,
        'error'   => 'Failed to prepare medical query',
    ), 500);
}

$stmt->bind_param('s', $regnum);
$stmt->execute();
$result = $stmt->get_result();
$med = $result->fetch_assoc();
$stmt->close();
$mysqli->close();

if (!$med) {
    api_send_json(array(
        'success' => false,
        'error'   => 'Medical record not found',
    ), 404);
}

api_send_json(array(
    'success' => true,
    'data'    => $med,
));

?>
