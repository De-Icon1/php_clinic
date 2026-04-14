<?php
/**
 * clinic_payment_callback.php
 * Receives the payment gateway callback from ticrms after a transaction.
 * Updates the local clinic_payment_transactions table with the result.
 */
session_start();
include('assets/inc/config.php');

// Accept both POST (gateway callback) and GET (browser redirect after payment)
$data    = array_merge($_GET, $_POST);
$transid = isset($data['request_id']) ? trim($data['request_id']) : '';
$status  = isset($data['status-code']) ? trim($data['status-code']) : (isset($data['status']) ? trim($data['status']) : '');
$msg     = isset($data['status-msg'])  ? trim($data['status-msg'])  : '';

if ($transid !== '') {
    // Map gateway status codes to readable values
    $statusLabel = 'UNKNOWN';
    if (in_array(strtoupper($status), ['00', '000', 'SUCCESS', 'SUCCESSFUL'])) {
        $statusLabel = 'SUCCESS';
    } elseif (in_array(strtoupper($status), ['FAILED', 'FAILURE', 'ERROR'])) {
        $statusLabel = 'FAILED';
    } elseif ($status !== '') {
        $statusLabel = strtoupper($status);
    }

    $stmt = $mysqli->prepare(
        "UPDATE clinic_payment_transactions SET status = ?, gateway_msg = ? WHERE transid = ?"
    );
    if ($stmt) {
        // Add gateway_msg column if it doesn't exist yet
        $mysqli->query("ALTER TABLE clinic_payment_transactions ADD COLUMN IF NOT EXISTS gateway_msg VARCHAR(255)");
        $mysqli->query("ALTER TABLE clinic_payment_transactions ADD COLUMN IF NOT EXISTS updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP");
        $stmt->bind_param('sss', $statusLabel, $msg, $transid);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch record for display
$row = null;
if ($transid !== '') {
    $r = $mysqli->prepare("SELECT * FROM clinic_payment_transactions WHERE transid = ? LIMIT 1");
    if ($r) {
        $r->bind_param('s', $transid);
        $r->execute();
        $result = $r->get_result();
        $row    = $result->fetch_assoc();
        $r->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<title>Payment Status</title>
<link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
<style>
  body { font-family: Arial, sans-serif; padding: 30px; }
  .result-box { max-width: 600px; margin: auto; border: 1px solid #ccc; padding: 25px; border-radius: 6px; }
  h4 { text-align: center; }
  table { width: 100%; }
  th { width: 40%; text-align: right; padding: 6px 10px; }
  td { text-align: left; padding: 6px 10px; }
  .success { color: green; font-weight: bold; }
  .failed  { color: red;   font-weight: bold; }
</style>
</head>
<body>
<div class="result-box">
  <h4>OOU HEALTH CENTRE – PAYMENT STATUS</h4>
  <?php if ($row): ?>
  <table>
    <tr><th>Transaction ID</th><td><?php echo htmlspecialchars($row['transid']); ?></td></tr>
    <tr><th>Patient ID</th>    <td><?php echo htmlspecialchars($row['regnum']); ?></td></tr>
    <tr><th>Patient Name</th>  <td><?php echo htmlspecialchars($row['patient_name']); ?></td></tr>
    <tr><th>Amount</th>        <td>&#8358;<?php echo number_format((float)$row['amount'], 2); ?></td></tr>
    <tr><th>Status</th>        <td class="<?php echo strtolower($row['status']) === 'success' ? 'success' : 'failed'; ?>">
      <?php echo htmlspecialchars($row['status']); ?>
    </td></tr>
    <?php if (!empty($row['gateway_msg'])): ?>
    <tr><th>Message</th>       <td><?php echo htmlspecialchars($row['gateway_msg']); ?></td></tr>
    <?php endif; ?>
  </table>
  <?php elseif ($transid !== ''): ?>
  <p>Transaction <strong><?php echo htmlspecialchars($transid); ?></strong> not found in local records.</p>
  <?php else: ?>
  <p style="color:red;">No transaction ID received.</p>
  <?php endif; ?>

  <div style="margin-top:20px; text-align:center;">
    <a href="cashier_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
  </div>
</div>
</body>
</html>
