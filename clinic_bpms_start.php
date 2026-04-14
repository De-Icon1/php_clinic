<?php
session_start();

// Simplified bridge: do NOT connect to any BPMS database from this
// clinic application. Just redirect to the central BPMS portal,
// optionally passing along basic details from the local payment.

if (!isset($_SESSION['bpms_type'], $_SESSION['bpms_amount'])) {
    echo "Missing BPMS payment context. Please start the payment again from the cashier page.";
    exit;
}

$type        = $_SESSION['bpms_type'];
$amount      = (float) $_SESSION['bpms_amount'];
$customer    = isset($_SESSION['bpms_customer']) ? trim($_SESSION['bpms_customer']) : '';
$patientCode = isset($_SESSION['bpms_patient_code']) ? trim($_SESSION['bpms_patient_code']) : '';
$trackid     = isset($_SESSION['bpms_trackid']) ? trim($_SESSION['bpms_trackid']) : '';
$teller      = isset($_SESSION['bpms_teller']) ? trim($_SESSION['bpms_teller']) : '';

if ($amount <= 0) {
    echo "Invalid payment amount for BPMS integration.";
    exit;
}

// Mark the session so that payment-invoice.php (on this same server)
// knows it was initiated from the clinic bridge.
$_SESSION['bpms_from_clinic'] = true;

// Redirect to the LOCAL payment-invoice.php on THIS server so the
// PHP session is preserved (cross-domain redirects lose the session).
$scheme  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'];
$dir     = rtrim(dirname($_SERVER['REQUEST_URI']), '/');
$localUrl = $scheme . '://' . $host . $dir . '/payment-invoice.php';

// Debug mode: when ?debug=1 is present, show the URL instead of redirecting.
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Local payment-invoice URL:\n" . $localUrl;
    exit;
}

header('Location: ' . $localUrl);
exit;
