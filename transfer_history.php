<?php
session_start();
include('assets/inc/config.php');

// Simple listing of transfer audit records
$res = $mysqli->query("SELECT t.*, u.doc_number AS user_doc FROM transfer_audit t LEFT JOIN his_docs u ON u.doc_id = t.user_id ORDER BY t.created_at DESC LIMIT 200");
?>
<!doctype html>
<html>
<head>
  <?php include('assets/inc/head.php'); ?>
  <title>Transfer History</title>
</head>
<body>
<?php include('assets/inc/nav_r.php'); ?>
<?php include('assets/inc/sidebar_admin.php'); ?>
<div class="content-page">
  <div class="content">
    <div class="container-fluid">
      <div class="row"><div class="col-12"><h4>Transfer History / Audit</h4></div></div>
      <div class="card"><div class="card-body">
        <table class="table table-sm table-striped">
          <thead><tr><th>#</th><th>When</th><th>User</th><th>Action</th><th>Item</th><th>Qty</th><th>From</th><th>To</th><th>Patient</th><th>Notes</th></tr></thead>
          <tbody>
          <?php $i=1; while($r = $res->fetch_assoc()) { ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($r['created_at']); ?></td>
              <td><?php echo htmlspecialchars($r['user_name'] ?: $r['user_doc'] ?: 'system'); ?></td>
              <td><?php echo htmlspecialchars($r['action']); ?></td>
              <td><?php echo htmlspecialchars($r['item_name']); ?></td>
              <td><?php echo intval($r['qty']); ?></td>
              <td><?php echo htmlspecialchars($r['from_table']); ?></td>
              <td><?php echo htmlspecialchars($r['to_table']); ?></td>
              <td><?php echo htmlspecialchars($r['patient_code']); ?></td>
              <td><?php echo htmlspecialchars($r['notes']); ?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div></div>
    </div>
  </div>
</div>
</body>
</html>
