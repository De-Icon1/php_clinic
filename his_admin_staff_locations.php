<?php
session_start();
include('assets/inc/config.php');
include('assets/inc/checklogins.php');
check_login();
authorize();

$aid        = isset($_SESSION['doc_id']) ? (int)$_SESSION['doc_id'] : 0;
$currentDept = isset($_SESSION['doc_dept']) ? trim($_SESSION['doc_dept']) : '';
$isAdmin    = (strtolower($currentDept) === 'administrator');

// Handle assignment form submit
if (isset($_POST['assign_location'])) {
    $staff_id    = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : 0;
    $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : 0;
    $reason      = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    if ($staff_id <= 0 || $location_id <= 0) {
        $err = 'Please select a staff member and a valid location.';
    } else {
        // Optional: ensure non-admins can only manage their own department
        if (!$isAdmin && $currentDept !== '') {
            $chk = $mysqli->prepare('SELECT COUNT(*) AS cnt FROM his_docs WHERE doc_id = ? AND doc_dept = ?');
            if ($chk) {
                $chk->bind_param('is', $staff_id, $currentDept);
                $chk->execute();
                $cres = $chk->get_result();
                $crow = $cres ? $cres->fetch_assoc() : null;
                $chk->close();
                if (!$crow || (int)$crow['cnt'] === 0) {
                    $err = 'You can only assign locations for staff in your own unit.';
                }
            }
        }

        if (empty($err)) {
            // Close existing active assignments for this staff
            $upd = $mysqli->prepare('UPDATE staff_locations SET is_active = 0, active_to = NOW() WHERE staff_id = ? AND is_active = 1');
            if ($upd) {
                $upd->bind_param('i', $staff_id);
                $upd->execute();
                $upd->close();
            }

            // Look up staff dept as role by default
            $role = null;
            $sinfo = $mysqli->prepare('SELECT doc_dept FROM his_docs WHERE doc_id = ? LIMIT 1');
            if ($sinfo) {
                $sinfo->bind_param('i', $staff_id);
                $sinfo->execute();
                $sres = $sinfo->get_result();
                if ($srow = $sres->fetch_assoc()) {
                    $role = $srow['doc_dept'];
                }
                $sinfo->close();
            }

            if ($role === null) {
                $role = '';
            }

            // Insert new active assignment
            $ins = $mysqli->prepare('INSERT INTO staff_locations (staff_id, location_id, role, active_from, is_active, assigned_by, reason) VALUES (?, ?, ?, NOW(), 1, ?, ?)');
            if ($ins) {
                $ins->bind_param('iisis', $staff_id, $location_id, $role, $aid, $reason);
                if ($ins->execute()) {
                    $success = 'Working location updated successfully.';
                } else {
                    $err = 'Failed to save staff location. Please try again.';
                }
                $ins->close();
            } else {
                $err = 'Unable to prepare staff location statement.';
            }
        }
    }
}

// Fetch all locations
$locations = [];
$lres = $mysqli->query('SELECT id, name FROM campus_locations ORDER BY name ASC');
if ($lres) {
    while ($row = $lres->fetch_assoc()) {
        $locations[] = $row;
    }
}

// Fetch staff list (admins see all, heads see only their unit)
$whereSql = '';
$paramsSql = '';
if (!$isAdmin && $currentDept !== '') {
    $whereSql = ' WHERE doc_dept = ? ';
}
$sql = 'SELECT d.doc_id, d.doc_number, d.doc_fname, d.doc_lname, d.doc_dept, cl.name AS location_name, sl.location_id
        FROM his_docs d
        LEFT JOIN staff_locations sl ON sl.staff_id = d.doc_id AND sl.is_active = 1
        LEFT JOIN campus_locations cl ON cl.id = sl.location_id' . $whereSql . ' ORDER BY d.doc_dept, d.doc_fname, d.doc_lname';

if ($isAdmin || $currentDept === '') {
    $stmt = $mysqli->prepare($sql);
} else {
    $stmt = $mysqli->prepare($sql);
}

if (!$stmt) {
    die('Failed to prepare staff query.');
}

if (!$isAdmin && $currentDept !== '') {
    $stmt->bind_param('s', $currentDept);
}

$stmt->execute();
$staffRes = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<?php include('assets/inc/head.php');?>

<body>

<div id="wrapper">

    <?php include('assets/inc/nav_r.php');?>
    <?php include('assets/inc/sidebar_admin.php');?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">Assign Staff Working Locations</h4>
                            <p class="text-muted mb-1">Admins can manage all staff; heads of unit can manage only their department.</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card-box">
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($err)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Staff Number</th>
                                        <th>Department</th>
                                        <th>Current Location</th>
                                        <th>Assign New Location</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $cnt = 1;
                                    while ($row = $staffRes->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo $cnt++; ?></td>
                                            <td><?php echo htmlspecialchars($row['doc_fname'] . ' ' . $row['doc_lname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['doc_number']); ?></td>
                                            <td><?php echo htmlspecialchars($row['doc_dept']); ?></td>
                                            <td><?php echo $row['location_name'] ? htmlspecialchars($row['location_name']) : '<em>None</em>'; ?></td>
                                            <td>
                                                <form method="post" class="form-inline">
                                                    <input type="hidden" name="staff_id" value="<?php echo (int)$row['doc_id']; ?>">
                                                    <select name="location_id" class="form-control mr-2" required>
                                                        <option value="">-- Select Location --</option>
                                                        <?php foreach ($locations as $loc): ?>
                                                            <option value="<?php echo (int)$loc['id']; ?>" <?php echo ((int)$row['location_id'] === (int)$loc['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($loc['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="text" name="reason" class="form-control mr-2" placeholder="Reason (optional)">
                                                    <button type="submit" name="assign_location" class="btn btn-primary btn-sm">Save</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php include('assets/inc/footer.php');?>
    </div>

</div>

<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>

</body>
</html>
