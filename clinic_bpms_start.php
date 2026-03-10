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

// Load BPMS configuration and helpers
// These files live in the sibling /bpms application and are the same
// ones used by new-transaction.php and payment-invoice.php.
@include_once "dbconfig.php";
@include_once "../bpms/bpms-dbconnect.php";
@include_once "../bpms/bpms-functions.php";

if (!isset($pydb) || !($pydb instanceof PDO)) {
    echo "Unable to connect to BPMS database. Please contact the administrator.";
    exit;
}
if (!function_exists('generateBPMSTransId')) {
    echo "BPMS helper functions are not available. Please contact the administrator.";
    exit;
}

// Derive basic invoice identity for BPMS from clinic context
$regnum = ($type === 'pharmacy') ? $trackid : $patientCode;
if ($regnum === '') {
    // Fall back to teller or a generic ID if patient/trackid is missing
    $regnum = $teller !== '' ? $teller : 'CLINIC-'.date('YmdHis');
}

// Split customer name into surname / first / middle to fit BPMS schema
$sname = 'PATIENT';
$fname = '';
$mname = '';
if ($customer !== '') {
    $parts = preg_split('/\s+/', $customer, 3);
    if (isset($parts[0]) && $parts[0] !== '') {
        $sname = strtoupper($parts[0]);
    }
    if (isset($parts[1])) {
        $fname = ucwords(strtolower($parts[1]));
    }
    if (isset($parts[2])) {
        $mname = ucwords(strtolower($parts[2]));
    }
}

$level   = 'NL';
$prog    = ($type === 'pharmacy') ? 'CLINIC-PHARMACY' : 'CLINIC-GENERAL';
$session = date('Y').'/'.(date('Y') + 1);

// For now, use generic contact details; BPMS requires non-empty values.
$email = 'clinic-payments@example.com';
$tel   = '0000000000';

// Encode clinic context into BPMS invoice remarks so we can reconcile later from bpms-report.php
$remarksParts = array(
    'CLINIC',
    $type,
    $teller,
    $patientCode,
    $trackid,
    $customer
);
$remarks = implode('|', $remarksParts);

// Locate the BPMS revenue row for this payment using the configured head
// The user indicated the revenue head text is "heakth center bill" (clinic health centre bill)
$headHint = 'heakth center bill';
$rev      = null;

try {
    // Prefer exact match on head, but allow case-insensitive partial match as fallback
    $stmt = $pydb->prepare("SELECT * FROM revenues WHERE head = :head LIMIT 1");
    $stmt->execute(array(':head' => $headHint));
    if ($stmt->rowCount() === 0) {
        $stmt = $pydb->prepare("SELECT * FROM revenues WHERE LOWER(head) LIKE LOWER(:h) LIMIT 1");
        $stmt->execute(array(':h' => '%'.$headHint.'%'));
    }
    if ($stmt->rowCount() > 0) {
        $rev = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $rev = null;
}

if (!$rev || !isset($rev['sn'], $rev['revcode'], $rev['head'])) {
    echo "Unable to locate BPMS revenue head for clinic payments (heakth center bill). Please configure the revenue in BPMS and try again.";
    exit;
}

$purpose = $rev['sn'];
$revcode = $rev['revcode'];
$head    = $rev['head'];

// Generate a BPMS transaction / invoice id and persist invoice row
$invoiceid        = generateBPMSTransId('INV');
$_SESSION['invid'] = $invoiceid;

try {
    $sql  = "INSERT INTO invoices (transid, regnum, sname, fname, mname, level, prog, session, email, tel, purpose, revcode, head, amount, remarks) "
          . "VALUES (:transid, :regnum, :sname, :fname, :mname, :level, :prog, :session, :email, :tel, :purpose, :revcode, :head, :amount, :remarks)";
    $stmt = $pydb->prepare($sql);
    $stmt->execute(array(
        ':transid' => $invoiceid,
        ':regnum'  => $regnum,
        ':sname'   => $sname,
        ':fname'   => $fname,
        ':mname'   => $mname,
        ':level'   => $level,
        ':prog'    => $prog,
        ':session' => $session,
        ':email'   => $email,
        ':tel'     => $tel,
        ':purpose' => $purpose,
        ':revcode' => $revcode,
        ':head'    => $head,
        ':amount'  => $amount,
        ':remarks' => $remarks,
    ));
} catch (Exception $e) {
    echo "Failed to create BPMS invoice: ".htmlspecialchars($e->getMessage());
    exit;
}

// Clear one-time clinic context keys; keep invid for payment-invoice.php
unset($_SESSION['bpms_type'], $_SESSION['bpms_amount'], $_SESSION['bpms_customer'], $_SESSION['bpms_patient_code'], $_SESSION['bpms_trackid'], $_SESSION['bpms_teller']);

// Hand off to the BPMS payment-invoice flow in this folder, which will in turn
// call preBPMSPurchaseLogger and redirect the user to the payment gateway.
header('Location: payment-invoice.php');
exit;
