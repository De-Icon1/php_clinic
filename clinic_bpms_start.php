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

// Redirect to the self-contained local payment form on this server.
// Session is preserved since we stay on the same domain.
$scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
$dir      = rtrim(dirname($_SERVER['REQUEST_URI']), '/');
$localUrl = $scheme . '://' . $host . $dir . '/clinic_payment_form.php';

// Debug mode: when ?debug=1 is present, show the URL instead of redirecting.
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Payment form URL:\n" . $localUrl;
    exit;
}

header('Location: ' . $localUrl);
exit;
