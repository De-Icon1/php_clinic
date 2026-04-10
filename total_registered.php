<?php
session_start();
include('assets/inc/config.php');

$campus_id = isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : null;

if (isset($_POST['genreport'])) {
    $datefrm = $_POST['datefrom'] ?? null;
    $dateto = $_POST['dateto'] ?? null;
    // build query
    $params = [];
    $types = '';
    $where = [];
    if ($datefrm && $dateto) {
        $where[] = "date BETWEEN ? AND ?";
    }
    if ($campus_id) {
        // check if sendsignal has campus_id
        $has = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
        if ($has) {
            $where[] = "campus_id = ?";
        }
    }

    $sql = "SELECT COUNT(DISTINCT pat_code) AS total FROM sendsignal";
    if (!empty($where)) { $sql .= ' WHERE ' . implode(' AND ', $where); }
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $bindParams = [];
        if ($datefrm && $dateto) { $bindParams[] = $datefrm; $bindParams[] = $dateto; }
        if ($campus_id) {
            $has = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
            if ($has) { $bindParams[] = $campus_id; }
        }
        if (!empty($bindParams)) {
            $types = str_repeat('s', count($bindParams));
            // convert last campus param type to integer if present
            if ($campus_id && $has) { $types = str_repeat('s', count($bindParams)-1) . 'i'; }
            $stmt->bind_param($types, ...$bindParams);
        }
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $total = $res['total'] ?? 0;
    } else {
        $total = 0;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include('assets/inc/head.php');?>
<body>
<div id="wrapper">
<?php include('assets/inc/nav_r.php');?>
<?php
// Use VC sidebar for Vice Chancellor, admin sidebar for others
$sidebar = 'assets/inc/sidebar_admin.php';
if (isset($_SESSION['doc_dept']) && strtolower(trim($_SESSION['doc_dept'])) === 'vice chancellor') {
    $sidebar = 'assets/inc/sidebar_vc.php';
}
include($sidebar);
?>
<div class="content-page"><div class="content"><div class="container-fluid">
    <div class="page-title-box"><h4 class="page-title">Total Registered Patients</h4></div>
    <div class="card"><div class="card-body">
        <form method="post">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Date From</label>
                    <input type="date" name="datefrom" class="form-control">
                </div>
                <div class="form-group col-md-3">
                    <label>Date To</label>
                    <input type="date" name="dateto" class="form-control">
                </div>
            </div>
            <button type="submit" name="genreport" class="btn btn-primary">View</button>
        </form>
        <?php if (isset($total)) { ?>
            <hr>
            <h5>Total Registered Patients: <?php echo intval($total); ?></h5>
        <?php } ?>
    </div></div>
</div></div></div>
<?php include('assets/inc/footer.php');?>
</div>
</body>
</html>
