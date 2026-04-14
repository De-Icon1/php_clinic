<?php
session_start();
include('assets/inc/config.php');
include('assets/inc/checklogins.php');
check_login();

// ── Gateway credentials (same as payments.oouagoiwoye.edu.ng) ──────────────
$merchant_id    = 300029;
$public_key     = 'PUB_KEY_WMZEYBRU1O1KRESPOPOOUYCDI4FTCUKJ';
$privateKey     = 'PRI_KEY_WMWERTYU7I900OOUHOKHDGDHDT32AGSHHJ';
$hash_type      = 'SHA256';
$currency       = 'NGN';
$paymentGateway = 'https://ticrms.edutams.net/checkout/entry';

// ── Validate session ──────────────────────────────────────────────────────
if (!isset($_SESSION['bpms_type'], $_SESSION['bpms_amount'])) {
    echo "<p style='color:red;font-family:sans-serif'>Missing payment context. Please start the payment again from the cashier page.</p>";
    exit;
}

$type        = $_SESSION['bpms_type'];
$amount      = (float) $_SESSION['bpms_amount'];
$customer    = isset($_SESSION['bpms_customer'])     ? trim($_SESSION['bpms_customer'])     : '';
$patientCode = isset($_SESSION['bpms_patient_code']) ? trim($_SESSION['bpms_patient_code']) : '';
$trackid     = isset($_SESSION['bpms_trackid'])      ? trim($_SESSION['bpms_trackid'])      : '';

if ($amount <= 0) {
    echo "<p style='color:red;font-family:sans-serif'>Invalid payment amount.</p>";
    exit;
}

// ── Build invoice fields ──────────────────────────────────────────────────
$regnum   = $patientCode !== '' ? $patientCode : 'CLINIC';
$revcode  = ($type === 'pharmacy') ? 'HMSPHARM' : 'HMSCLINIC';
$head     = ($type === 'pharmacy') ? 'Hospital Pharmacy Payment' : 'Hospital Clinic Payment';
$session  = date('Y') . '/' . (date('Y') + 1);
$level    = 0;
$email    = 'clinic@oouagoiwoye.edu.ng'; // placeholder – ticrms requires non-empty
$tel      = '08000000000';               // placeholder – ticrms requires 11 digits

// Split customer into name parts
$nameParts = preg_split('/\s+/', $customer);
$sname = isset($nameParts[0]) && $nameParts[0] !== '' ? strtoupper($nameParts[0])        : 'CLINIC';
$fname = isset($nameParts[1])                         ? ucwords(strtolower($nameParts[1])): '';
$mname = isset($nameParts[2])                         ? ucwords(strtolower($nameParts[2])): '';
$fullName = trim($sname . ' ' . $fname . ' ' . $mname);

// ── Generate a unique transaction ID ─────────────────────────────────────
$transid = 'CLINIC' . date('YmdHis') . mt_rand(10, 99);

// ── Callback URL on THIS server ───────────────────────────────────────────
$scheme       = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$callback_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/clinic_payment_callback.php';

// ── Build HMAC hash (same algorithm as preBPMSPurchaseLogger) ────────────
$hash_string = "request_id={$transid}"
    . "&callback_url={$callback_url}"
    . "&customer_email={$email}"
    . "&customer_phone={$tel}"
    . "&customer_first_name={$fname}"
    . "&customer_first_name={$sname}"
    . "&public-key={$public_key}";
$hash = hash_hmac($hash_type, $hash_string, $privateKey);

// ── Misc display fields ───────────────────────────────────────────────────
$product_desc  = $regnum . '|' . $level . '|CLINIC|' . $session . '|' . $revcode . '|' . $head;
$bill_desc     = $regnum . ' | ' . $fullName . ' | ' . $head . ' | ' . $level . ' Level';
$h             = 1;
$regdate       = gmdate('d/m/Y g:i:s A', time() + ($h * 3600));

// ── Save transaction to local DB so cashier can reconcile ─────────────────
$mysqli->query("CREATE TABLE IF NOT EXISTS clinic_payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transid VARCHAR(50) NOT NULL,
    regnum VARCHAR(50),
    patient_name VARCHAR(200),
    amount DECIMAL(10,2),
    type VARCHAR(50),
    trackid VARCHAR(100),
    status VARCHAR(20) DEFAULT 'PENDING',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$stmt = $mysqli->prepare(
    "INSERT INTO clinic_payment_transactions (transid, regnum, patient_name, amount, type, trackid) VALUES (?,?,?,?,?,?)"
);
$stmt->bind_param('sssdss', $transid, $regnum, $fullName, $amount, $type, $trackid);
$stmt->execute();
$stmt->close();

// ── Clear session so it is not reused ─────────────────────────────────────
unset(
    $_SESSION['bpms_from_clinic'],
    $_SESSION['bpms_type'],
    $_SESSION['bpms_amount'],
    $_SESSION['bpms_customer'],
    $_SESSION['bpms_patient_code'],
    $_SESSION['bpms_trackid'],
    $_SESSION['bpms_teller']
);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<title><?php echo htmlspecialchars($transid); ?> – HMS PAYMENT INVOICE</title>
<link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
<style>
  body { font-family: Arial, sans-serif; padding: 20px; }
  .invoice-box { max-width: 750px; margin: auto; border: 1px solid #ccc; padding: 20px; }
  .invoice-box table { width: 100%; border-collapse: collapse; }
  .invoice-box td, .invoice-box th { padding: 6px 10px; }
  .invoice-box th { text-align: right; width: 35%; }
  .invoice-box td { text-align: left; }
  h4 { text-align: center; }
  .amount-row td { font-weight: bold; font-size: 1.1em; }
  .btn-pay { width: 100%; padding: 12px; font-size: 1.1em; margin-top: 15px; }
  @media print { .no-print { display: none; } }
</style>
</head>
<body>
<div class="invoice-box">
  <h4>OOU HEALTH CENTRE – PAYMENT INVOICE</h4>
  <h4><?php echo htmlspecialchars($transid); ?></h4>
  <table>
    <tr><th>Transaction ID</th><td><?php echo htmlspecialchars($transid); ?></td></tr>
    <tr><th>Patient ID / Reg. No</th><td><?php echo htmlspecialchars($regnum); ?></td></tr>
    <tr><th>Patient Name</th><td><?php echo htmlspecialchars($fullName); ?></td></tr>
    <tr><th>Payment Type</th><td><?php echo htmlspecialchars($head); ?></td></tr>
    <tr><th>Revenue Code</th><td><?php echo htmlspecialchars($revcode); ?></td></tr>
    <tr><th>Session</th><td><?php echo htmlspecialchars($session); ?></td></tr>
    <tr class="amount-row"><th>Amount Payable</th><td>&#8358;<?php echo number_format($amount, 2); ?></td></tr>
  </table>

  <div class="no-print" style="margin-top:20px;">
    <button onclick="window.print()" class="btn btn-info btn-pay">Print Invoice</button>

    <form method="post" action="<?php echo htmlspecialchars($paymentGateway); ?>" style="margin-top:10px;">
      <input type="hidden" name="billed_amount"       value="<?php echo htmlspecialchars($amount); ?>"/>
      <input type="hidden" name="name"                value="<?php echo htmlspecialchars($fullName); ?>"/>
      <input type="hidden" name="school_code"         value="<?php echo htmlspecialchars($merchant_id); ?>"/>
      <input type="hidden" name="date"                value="<?php echo htmlspecialchars($regdate); ?>"/>
      <input type="hidden" name="bill_description"    value="<?php echo htmlspecialchars($bill_desc); ?>"/>
      <input type="hidden" name="customer_phone"      value="<?php echo htmlspecialchars($tel); ?>"/>
      <input type="hidden" name="customer_id"         value="<?php echo htmlspecialchars($regnum); ?>"/>
      <input type="hidden" name="customer_first_name" value="<?php echo htmlspecialchars($fname); ?>"/>
      <input type="hidden" name="customer_last_name"  value="<?php echo htmlspecialchars($sname); ?>"/>
      <input type="hidden" name="customer_address"    value="NAN"/>
      <input type="hidden" name="customer_fname"      value="<?php echo htmlspecialchars($fname); ?>"/>
      <input type="hidden" name="public_key"          value="<?php echo htmlspecialchars($public_key); ?>"/>
      <input type="hidden" name="request_id"          value="<?php echo htmlspecialchars($transid); ?>"/>
      <input type="hidden" name="revenue_code"        value="<?php echo htmlspecialchars($revcode); ?>"/>
      <input type="hidden" name="currency"            value="<?php echo htmlspecialchars($currency); ?>"/>
      <input type="hidden" name="callback_url"        value="<?php echo htmlspecialchars($callback_url); ?>"/>
      <input type="hidden" name="product-desc"        value="<?php echo htmlspecialchars($product_desc); ?>"/>
      <input type="hidden" name="customer_email"      value="<?php echo htmlspecialchars($email); ?>"/>
      <input type="hidden" name="hash_type"           value="<?php echo htmlspecialchars($hash_type); ?>"/>
      <input type="hidden" name="hash"                value="<?php echo htmlspecialchars($hash); ?>"/>
      <button type="submit" class="btn btn-success btn-pay">PROCEED TO WEB PAYMENT GATEWAY</button>
    </form>
  </div>
</div>
</body>
</html>
