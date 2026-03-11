<?php
session_start();

// Bridge from clinic cashier payments directly into the BPMS payment gateway,
// using the details from the local accounting section. This bypasses the
// clinic copies of new-transaction.php and payment-invoice.php and sends
// the user straight to the BPMS gateway.

// Expected session values from cashier_payment.php:
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

// Load BPMS configuration and helpers used by the main BPMS portal.
// These files are expected to live in the sibling /bpms application,
// just like in the original new-transaction.php and payment-invoice.php.
@include_once 'dbconfig.php';
@include_once '../bpms/bpms-dbconnect.php';
@include_once '../bpms/bpms-functions.php';

if (!isset($pydb) || !($pydb instanceof PDO)) {
    echo "Unable to connect to BPMS database. Please contact the administrator.";
    exit;
}

if (!function_exists('generateBPMSTransId') || !function_exists('preBPMSPurchaseLogger')) {
    echo "BPMS helper functions are not available. Please contact the administrator.";
    exit;
}

// Map clinic payment details into the BPMS invoice structure.
$regnum = ($type === 'pharmacy') ? $trackid : $patientCode;
if ($regnum === '') {
    // Fall back to teller or a generic ID if patient/trackid is missing
    $regnum = $teller !== '' ? $teller : 'CLINIC-' . date('YmdHis');
}

// Split customer name into surname / first / middle to fit BPMS schema.
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
$session = date('Y') . '/' . (date('Y') + 1);

// Basic contact details; you can later enhance this to pull real
// patient email/phone from the clinic database if needed.
$email = 'clinic-payments@example.com';
$tel   = '0000000000';

// Locate the BPMS revenue row for this payment using the configured head.
// The user indicated the revenue head text is "heakth center bill".
$headHint = 'heakth center bill';
$rev      = null;

try {
    $stmt = $pydb->prepare('SELECT * FROM revenues WHERE head = :head LIMIT 1');
    $stmt->execute(array(':head' => $headHint));
    if ($stmt->rowCount() === 0) {
        $stmt = $pydb->prepare('SELECT * FROM revenues WHERE LOWER(head) LIKE LOWER(:h) LIMIT 1');
        $stmt->execute(array(':h' => '%' . $headHint . '%'));
    }
    if ($stmt->rowCount() > 0) {
        $rev = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $rev = null;
}

if (!$rev || !isset($rev['sn'], $rev['revcode'], $rev['head'])) {
    echo 'Unable to locate BPMS revenue head for clinic payments (heakth center bill). Please configure the revenue in BPMS and try again.';
    exit;
}

$purpose = $rev['sn'];
$revcode = $rev['revcode'];
$head    = $rev['head'];

// Generate a BPMS transaction / invoice id and persist invoice row in BPMS.
$invoiceid         = generateBPMSTransId('INV');
$_SESSION['invid'] = $invoiceid;

try {
    $sql  = 'INSERT INTO invoices (transid, regnum, sname, fname, mname, level, prog, session, email, tel, purpose, revcode, head, amount, remarks) '
          . 'VALUES (:transid, :regnum, :sname, :fname, :mname, :level, :prog, :session, :email, :tel, :purpose, :revcode, :head, :amount, :remarks)';
    $stmt = $pydb->prepare($sql);
    $remarksParts = array('CLINIC', $type, $teller, $patientCode, $trackid, $customer);
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
        ':remarks' => implode('|', $remarksParts),
    ));
} catch (Exception $e) {
    echo 'Failed to create BPMS invoice: ' . htmlspecialchars($e->getMessage());
    exit;
}

// Prepare to send the user straight to the BPMS gateway using the same
// approach as payment-invoice.php (preBPMSPurchaseLogger + POST form).

// Build referrer and callback URLs as in the original BPMS integration.
$referrer_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$callback_url = 'https://payments.oouagoiwoye.edu.ng/bpms-report.php';

$product_id   = $revcode;
$product_desc = $regnum . '|' . $level . '|' . $prog . '|' . $session . '|' . $revcode . '|' . $head;

// These globals ($currency, $merchant_id, $hash_type, $public_key,
// $privateKey, $paymentGateway) are expected to be defined by the
// included BPMS configuration files.

$details = preBPMSPurchaseLogger(
    $regnum,
    $sname,
    $fname,
    $mname,
    $level,
    $prog,
    $session,
    'PYM',
    $product_id,
    $referrer_url,
    $callback_url,
    $product_desc,
    $amount,
    $currency,
    $email,
    $merchant_id,
    $hash_type,
    $public_key,
    $privateKey,
    $invoiceid,
    $tel,
    'WEB',
    $paymentGateway
);

if ($details === 'FAILED' || !is_array($details)) {
    echo 'UNABLE TO VALIDATE TRANSACTION WITH BPMS. PLEASE TRY AGAIN LATER.';
    exit;
}

// Clear one-time clinic context keys so they are not reused accidentally
unset($_SESSION['bpms_type'], $_SESSION['bpms_amount'], $_SESSION['bpms_customer'], $_SESSION['bpms_patient_code'], $_SESSION['bpms_trackid'], $_SESSION['bpms_teller']);

// Output a minimal auto-submitting form that sends the user directly to
// the BPMS web payment gateway.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to Payment Gateway...</title>
</head>
<body onload="document.getElementById('bpms-pay-form').submit();">
    <p>Please wait, redirecting to the payment gateway...</p>
    <form id="bpms-pay-form" method="post" action="<?php echo htmlspecialchars($paymentGateway); ?>">
        <input type="hidden" name="billed_amount" value="<?php echo htmlspecialchars($details['amount_due']); ?>" />
        <input type="hidden" name="name" value="<?php echo htmlspecialchars(strtoupper($sname) . ' ' . ucwords(strtolower($fname)) . ' ' . ucwords(strtolower($mname))); ?>" />
        <input type="hidden" name="school_code" value="<?php echo htmlspecialchars($details['merchant_id']); ?>" />
        <input type="hidden" name="date" value="<?php
            $h  = 1;
            $hm = $h * 60;
            $ms = $hm * 60;
            echo gmdate('d/m/Y g:i:s A', time() + $ms);
        ?>" />
        <input type="hidden" name="bill_description" value="<?php echo htmlspecialchars($product_desc); ?>" />
        <input type="hidden" name="customer_phone" value="<?php echo htmlspecialchars($tel); ?>" />
        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($regnum); ?>" />
        <input type="hidden" name="customer_first_name" value="<?php echo htmlspecialchars($fname); ?>" />
        <input type="hidden" name="customer_last_name" value="<?php echo htmlspecialchars($sname); ?>" />
        <input type="hidden" name="customer_address" value="<?php echo 'NAN'; ?>" />
        <input type="hidden" name="customer_fname" value="<?php echo htmlspecialchars($fname); ?>" />
        <input type="hidden" name="public_key" value="<?php echo htmlspecialchars($public_key); ?>" />
        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($details['transid']); ?>" />
        <input type="hidden" name="revenue_code" value="<?php echo htmlspecialchars($details['product_id']); ?>" />
        <input type="hidden" name="currency" value="<?php echo htmlspecialchars($details['currency']); ?>" />
        <input type="hidden" name="callback_url" value="<?php echo htmlspecialchars($details['callback_url']); ?>" />
        <input type="hidden" name="product-desc" value="<?php echo htmlspecialchars($details['product_desc']); ?>" />
        <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($details['customer_email']); ?>" />
        <input type="hidden" name="hash_type" value="<?php echo htmlspecialchars($details['hash_type']); ?>" />
        <input type="hidden" name="hash" value="<?php echo htmlspecialchars($details['hash']); ?>" />
        <noscript>
            <button type="submit">Click here if you are not redirected automatically</button>
        </noscript>
    </form>
</body>
</html>
