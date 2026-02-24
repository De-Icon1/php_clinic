<?php
session_start();
include('assets/inc/config.php');

$campus_id = isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<?php include('assets/inc/head.php');?>
<body>
<div id="wrapper">
<?php include('assets/inc/nav_r.php');?>
<?php include('assets/inc/sidebar_admin.php');?>
<div class="content-page"><div class="content"><div class="container-fluid">
    <div class="page-title-box"><h4 class="page-title">Current Admitted Patients</h4></div>
    <div class="card"><div class="card-body">
        <?php
            // Build query for inpatients
            $where = [];
            if ($campus_id) {
                $has = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='his_patients' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
                if ($has) { $where[] = "campus_id = " . (int)$campus_id; }
            }
            $sql = "SELECT * FROM his_patients WHERE pat_type = 'InPatient'";
            if (!empty($where)) { $sql .= ' AND ' . implode(' AND ', $where); }
            $sql .= " ORDER BY pat_id DESC";
            $res = $mysqli->query($sql);
        ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>#</th><th>Name</th><th>Patient No</th><th>Address</th><th>Phone</th><th>Age</th></tr></thead>
                <tbody>
                <?php if ($res) { $i=1; while ($row = $res->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td><?php echo htmlspecialchars($row['pat_fname'] . ' ' . $row['pat_lname']); ?></td>
                        <td><?php echo htmlspecialchars($row['pat_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['pat_addr']); ?></td>
                        <td><?php echo htmlspecialchars($row['pat_phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['pat_age']); ?> Years</td>
                    </tr>
                <?php } } else { echo '<tr><td colspan="6">No records found</td></tr>'; } ?>
                </tbody>
            </table>
        </div>
    </div></div>
</div></div></div>
<?php include('assets/inc/footer.php');?>
</div>
</body>
</html>
