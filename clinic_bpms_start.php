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

// Handover directly to the central BPMS payment-invoice endpoint
// on the payments server. The remote script is responsible for
// generating any invoice/transaction records as needed.
$bpmsUrl = 'https://payments.oouagoiwoye.edu.ng/payment-invoice.php';

// Build simple query parameters with the information we have.
$params = array();

// These parameter names may or may not be used by the BPMS portal,
// but including them is harmless and lets the BPMS team wire them up
// later if desired.
$params['src']    = 'clinic';
$params['amount'] = $amount;
if ($customer !== '') {
    $params['name'] = $customer;
}
if ($patientCode !== '') {
    $params['regnum'] = $patientCode;
}
if ($trackid !== '') {
    $params['trackid'] = $trackid;
}
if ($teller !== '') {
    $params['ref'] = $teller;
}

$query   = http_build_query($params);
$fullUrl = $bpmsUrl . '?' . $query;

// Debug mode: when ?debug=1 is present, show the URL instead of redirecting.
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "BPMS redirect URL:\n" . $fullUrl;
    exit;
}

header('Location: ' . $fullUrl);
exit;
