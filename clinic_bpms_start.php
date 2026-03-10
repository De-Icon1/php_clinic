<?php
session_start();

// Bridge from clinic cashier payments into BPMS invoice/payment flow.
// Expects the following session values to be set by cashier_payment.php:
//  - bpms_type: 'general' or 'pharmacy'
//  - bpms_amount: numeric amount to charge
//  - bpms_customer: customer's full name
//  - bpms_patient_code: (optional) clinic patient code / ID
//  - bpms_trackid: (optional) pharmacy trackid for orders
//  - bpms_teller: (optional) local invoice/reference number

if (!isset($_SESSION['bpms_type'], $_SESSION['bpms_amount'])) {
    echo "Missing BPMS payment context. Please start the payment again from the cashier page.";
    exit;
}

// Read but do not currently use context; it is here in case you later
// decide to pass it through to the external BPMS portal as query params.
$type          = $_SESSION['bpms_type'];
$amount        = (float) $_SESSION['bpms_amount'];
$customer      = isset($_SESSION['bpms_customer']) ? trim($_SESSION['bpms_customer']) : '';
$patientCode   = isset($_SESSION['bpms_patient_code']) ? trim($_SESSION['bpms_patient_code']) : '';
$trackid       = isset($_SESSION['bpms_trackid']) ? trim($_SESSION['bpms_trackid']) : '';
$teller        = isset($_SESSION['bpms_teller']) ? trim($_SESSION['bpms_teller']) : '';

if ($amount <= 0) {
    echo "Invalid payment amount for BPMS integration.";
    exit;
}

// At this point we simply redirect the user to the central BPMS portal
// to complete the payment there. This avoids needing a local BPMS
// database connection inside the clinic application.

// Base URL of the BPMS invoice/payment portal
$bpmsUrl = 'https://payments.oouagoiwoye.edu.ng/new-transaction.php';

// Clear one-time clinic context keys so they are not reused accidentally
unset($_SESSION['bpms_type'], $_SESSION['bpms_amount'], $_SESSION['bpms_customer'], $_SESSION['bpms_patient_code'], $_SESSION['bpms_trackid'], $_SESSION['bpms_teller']);

header('Location: ' . $bpmsUrl);
exit;
