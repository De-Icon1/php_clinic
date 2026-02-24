<?php
session_start();
include('assets/inc/config.php');

// Provide CSV format template for scan consumables import
if (isset($_GET['download_format'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="scan_stock_format.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['name','category','campus_name','quantity','supplier','lpo']);
    fputcsv($out, ['Gel','Consumable','Main Store','50','ScanSupply Co','LPO-SCAN-001']);
    fclose($out);
    exit();
}

// Ensure scan_consumable_stock has supplier_name and lpo_ref columns (for older DBs)
$c1 = $mysqli->query("SHOW COLUMNS FROM scan_consumable_stock LIKE 'supplier_name'");
if (!$c1 || $c1->num_rows == 0) {
    @ $mysqli->query("ALTER TABLE scan_consumable_stock ADD COLUMN supplier_name VARCHAR(255) DEFAULT NULL");
}
$c2 = $mysqli->query("SHOW COLUMNS FROM scan_consumable_stock LIKE 'lpo_ref'");
if (!$c2 || $c2->num_rows == 0) {
    @ $mysqli->query("ALTER TABLE scan_consumable_stock ADD COLUMN lpo_ref VARCHAR(100) DEFAULT NULL");
}

/* ==================================================
   FETCH CAMPUSES AND MAP IDS
================================================== */
$campuses = [];
$main_store_id = null;

$qC = $mysqli->query("SELECT * FROM campus_locations ORDER BY name ASC");
while ($row = $qC->fetch_assoc()) {
    $campuses[$row['name']] = $row['id'];
}

// Ensure Main Store exists safely
if (isset($campuses['Main Store'])) {
    $main_store_id = $campuses['Main Store'];
} else {
    $mysqli->query("INSERT INTO campus_locations (name) VALUES ('Main Store') ON DUPLICATE KEY UPDATE name=name");
    $main_store_id = $mysqli->insert_id;
    if ($main_store_id == 0) {
        $res = $mysqli->query("SELECT id FROM campus_locations WHERE name='Main Store'")->fetch_assoc();
        $main_store_id = $res['id'];
    }
    $campuses['Main Store'] = $main_store_id;
}

/* ==================================================
   1. REGISTER NEW SCAN CONSUMABLE
================================================== */
if (isset($_POST['add_consumable'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];

    $stmt = $mysqli->prepare("INSERT INTO scan_consumables (name, category) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $category);
    $stmt->execute();
    if ($stmt) $success = "Scan consumable added successfully!";
    else $err = "Failed to add scan consumable.";
}

/* ==================================================
   2. ADD STOCK TO MAIN STORE
================================================== */
if (isset($_POST['add_stock'])) {
    $consumable_id = $_POST['consumable_id'];
    $qty = (int)$_POST['qty'];
    $supplier = isset($_POST['supplier']) ? trim($_POST['supplier']) : null;
    $lpo = isset($_POST['lpo']) ? trim($_POST['lpo']) : null;

    $chk = $mysqli->prepare("SELECT id, quantity FROM scan_consumable_stock WHERE consumable_id=? AND campus_id=?");
    $chk->bind_param("ii", $consumable_id, $main_store_id);
    $chk->execute();
    $r = $chk->get_result()->fetch_assoc();

    if ($r) {
        $new_qty = $r['quantity'] + $qty;
        $upd = $mysqli->prepare("UPDATE scan_consumable_stock SET quantity=?, supplier_name=?, lpo_ref=? WHERE id=?");
        if ($upd) {
            $upd->bind_param("issi", $new_qty, $supplier, $lpo, $r['id']);
            $upd->execute();
        }
    } else {
        $ins = $mysqli->prepare("INSERT INTO scan_consumable_stock (consumable_id, campus_id, quantity, supplier_name, lpo_ref) VALUES (?, ?, ?, ?, ?)");
        if ($ins) {
            $ins->bind_param("iiiss", $consumable_id, $main_store_id, $qty, $supplier, $lpo);
            $ins->execute();
        }
    }
    $success = "Scan stock added to Main Store!";
}

/* ==================================================
   3. TRANSFER STOCK (ISSUE / RETURN)
================================================== */
if (isset($_POST['transfer'])) {
    $consumable_id = $_POST['consumable_id'];
    $campus_id     = $_POST['campus_id'];
    $qty           = (int)$_POST['qty'];
    $action        = $_POST['action'];

    $main_stock = $mysqli->query("SELECT quantity FROM scan_consumable_stock WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
    $main_qty = ($main_stock->num_rows) ? $main_stock->fetch_assoc()['quantity'] : 0;

    $camp_stock = $mysqli->query("SELECT quantity FROM scan_consumable_stock WHERE consumable_id=$consumable_id AND campus_id=$campus_id");
    $camp_qty = ($camp_stock->num_rows) ? $camp_stock->fetch_assoc()['quantity'] : 0;

    if ($action == "issue") {
        if ($main_qty < $qty) $err = "Not enough stock in Main Store!";
        else {
            $mysqli->query("UPDATE scan_consumable_stock SET quantity=quantity-$qty WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
            $mysqli->query("INSERT INTO scan_consumable_stock (consumable_id, campus_id, quantity) VALUES ($consumable_id, $campus_id, $qty) ON DUPLICATE KEY UPDATE quantity=quantity+$qty");
            $success = "Issued to campus successfully!";
        }
    } elseif ($action == "return") {
        if ($camp_qty < $qty) $err = "Campus does not have enough stock!";
        else {
            $mysqli->query("UPDATE scan_consumable_stock SET quantity=quantity-$qty WHERE consumable_id=$consumable_id AND campus_id=$campus_id");
            $mysqli->query("UPDATE scan_consumable_stock SET quantity=quantity+$qty WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
            $success = "Returned to Main Store successfully!";
        }
    }
}

/* ==================================================
   4. QUICK ISSUE / RETURN MODAL
================================================== */
if (isset($_POST['scan_quick_issue'])) {
    $consumable_id = $_POST['consumable_id'];
    $campus_id     = $_POST['campus_id'];
    $qty           = (int)$_POST['qty'];
    $action        = $_POST['action_type'];

    $main_stock = $mysqli->query("SELECT quantity FROM scan_consumable_stock WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
    $main_qty = ($main_stock->num_rows) ? $main_stock->fetch_assoc()['quantity'] : 0;

    $camp_stock = $mysqli->query("SELECT quantity FROM scan_consumable_stock WHERE consumable_id=$consumable_id AND campus_id=$campus_id");
    $camp_qty = ($camp_stock->num_rows) ? $camp_stock->fetch_assoc()['quantity'] : 0;

    if ($action == "issue") {
        if ($main_qty < $qty) $err = "Not enough stock in Main Store!";
        else {
            $mysqli->query("UPDATE scan_consumable_stock SET quantity=quantity-$qty WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
            $mysqli->query("INSERT INTO scan_consumable_stock (consumable_id, campus_id, quantity) VALUES ($consumable_id, $campus_id, $qty) ON DUPLICATE KEY UPDATE quantity=quantity+$qty");
            $success = "Issued successfully!";
        }
    } elseif ($action == "return") {
        if ($camp_qty < $qty) $err = "Not enough stock at campus!";
        else {
            $mysqli->query("UPDATE scan_consumable_stock SET quantity=quantity-$qty WHERE consumable_id=$consumable_id AND campus_id=$campus_id");
            $mysqli->query("UPDATE scan_consumable_stock SET quantity=quantity+$qty WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
            $success = "Returned successfully!";
        }
    }
}

/* ==================================================
   CSV EXPORT / IMPORT
================================================== */
if(isset($_GET['export_scan_stock'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="scan_stock.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Consumable Name','Category','Campus','Quantity','Supplier','Reference/LPO']);
    $sql = "SELECT s.quantity, cl.name AS campus_name, c.name AS consumable_name, c.category, s.supplier_name, s.lpo_ref
            FROM scan_consumable_stock s
            JOIN scan_consumables c ON c.id = s.consumable_id
            JOIN campus_locations cl ON cl.id = s.campus_id
            ORDER BY cl.name ASC, c.name ASC";
    $res = $mysqli->query($sql);
    while($row = $res->fetch_assoc()) {
        fputcsv($output, [$row['consumable_name'], $row['category'], $row['campus_name'], $row['quantity'], $row['supplier_name'] ?? '', $row['lpo_ref'] ?? '']);
    }
    fclose($output);
    exit();
}

if(isset($_POST['import_scan_stock'])){
    $file = $_FILES['csv_file']['tmp_name'];
    if(($handle = fopen($file, "r")) !== FALSE){
        $row = 0;
        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
            if($row++ == 0) continue; // skip header
            // Expect: name, category, campus_name, quantity, supplier(optional), lpo(optional)
            $name = $data[0] ?? '';
            $category = $data[1] ?? '';
            $campus_name = $data[2] ?? '';
            $quantity = isset($data[3]) ? (int)$data[3] : 0;
            $supplier = $data[4] ?? null;
            $lpo = $data[5] ?? null;
            $stmt = $mysqli->prepare("SELECT id FROM scan_consumables WHERE name=? AND category=?");
            $stmt->bind_param("ss", $name, $category);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $consumable_id = $res['id'] ?? null;
            if(!$consumable_id) continue;
            $campus = $mysqli->prepare("SELECT id FROM campus_locations WHERE name=?");
            $campus->bind_param("s", $campus_name);
            $campus->execute();
            $res = $campus->get_result()->fetch_assoc();
            $campus_id = $res['id'] ?? null;
            if(!$campus_id) continue;
            $check = $mysqli->prepare("SELECT id FROM scan_consumable_stock WHERE consumable_id=? AND campus_id=?");
            $check->bind_param("ii", $consumable_id, $campus_id);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();
            if($existing){
                $update = $mysqli->prepare("UPDATE scan_consumable_stock SET quantity=?, supplier_name=?, lpo_ref=? WHERE id=?");
                $update->bind_param("issi", $quantity, $supplier, $lpo, $existing['id']);
                $update->execute();
            } else {
                $insert = $mysqli->prepare("INSERT INTO scan_consumable_stock (consumable_id, campus_id, quantity, supplier_name, lpo_ref) VALUES (?,?,?,?,?)");
                $insert->bind_param("iiiss", $consumable_id, $campus_id, $quantity, $supplier, $lpo);
                $insert->execute();
            }
        }
        fclose($handle);
        $success = "Scan stock imported successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

    <!--Head Code-->
    <?php include("assets/inc/head.php");?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
            <?php include("assets/inc/nav_r.php"); ?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
            <?php include("assets/inc/sidebar_admin.php");?>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title">Scan Consumables Store</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                        <?php if(isset($err)) echo "<div class='alert alert-danger'>$err</div>"; ?>

                        <!-- CSV Import / Export -->
                        <div class="mb-2">
                            <form method="post" enctype="multipart/form-data" class="d-inline">
                                <input type="file" name="csv_file" required>
                                <button type="submit" name="import_scan_stock" class="btn btn-success">Import Scan Stock CSV</button>
                            </form>
                            <a href="scan_consumables.php?export_scan_stock=1" class="btn btn-info">Export Scan Stock CSV</a>
                            <a href="scan_consumables.php?download_format=1" class="btn btn-secondary">Download CSV Format</a>
                        </div>

                        <!-- Register Scan Consumable -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h4>Register Scan Consumable</h4>
                                <form method="post">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <input type="text" name="name" class="form-control" placeholder="Name" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <input type="text" name="category" class="form-control" placeholder="Category" required>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" name="add_consumable">Add Consumable</button>
                                </form>
                            </div>
                        </div>

                        <!-- Add Stock to Main Store -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h4>Add Stock to Main Store</h4>
                                <form method="post">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Consumable</label>
                                            <select name="consumable_id" class="form-control" required>
                                                <option value="">-- Select Consumable --</option>
                                                <?php
                                                $q = $mysqli->query("SELECT id, name, category FROM scan_consumables ORDER BY name ASC");
                                                while($r = $q->fetch_assoc()){
                                                    echo "<option value='".intval($r['id'])."'>".htmlspecialchars($r['name'])." (".htmlspecialchars($r['category']).")</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Quantity</label>
                                            <input type="number" name="qty" min="1" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Supplier</label>
                                            <input type="text" name="supplier" class="form-control">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>LPO / Reference</label>
                                            <input type="text" name="lpo" class="form-control">
                                        </div>
                                    </div>
                                    <button class="btn btn-success" name="add_stock">Add to Main Store</button>
                                </form>
                            </div>
                        </div>

                        <!-- Transfer Stock Between Main Store and Campus -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h4>Transfer Stock</h4>
                                <form method="post">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Consumable</label>
                                            <select name="consumable_id" class="form-control" required>
                                                <option value="">-- Select Consumable --</option>
                                                <?php
                                                $q = $mysqli->query("SELECT id, name, category FROM scan_consumables ORDER BY name ASC");
                                                while($r = $q->fetch_assoc()){
                                                    echo "<option value='".intval($r['id'])."'>".htmlspecialchars($r['name'])." (".htmlspecialchars($r['category']).")</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Campus</label>
                                            <select name="campus_id" class="form-control" required>
                                                <option value="">-- Select Campus --</option>
                                                <?php
                                                $camp = $mysqli->query("SELECT id, name FROM campus_locations ORDER BY name ASC");
                                                while($c = $camp->fetch_assoc()){
                                                    echo "<option value='".intval($c['id'])."'>".htmlspecialchars($c['name'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Quantity</label>
                                            <input type="number" name="qty" min="1" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Action</label>
                                            <select name="action" class="form-control" required>
                                                <option value="issue">Issue from Main Store to Campus</option>
                                                <option value="return">Return from Campus to Main Store</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" name="transfer">Transfer</button>
                                </form>
                            </div>
                        </div>

                        <!-- Simple Stock Overview -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h4>Scan Stock by Campus</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Campus</th>
                                                <th>Consumable</th>
                                                <th>Category</th>
                                                <th>Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT cl.name AS campus_name, c.name AS consumable_name, c.category, s.quantity
                                                    FROM scan_consumable_stock s
                                                    JOIN scan_consumables c ON c.id = s.consumable_id
                                                    JOIN campus_locations cl ON cl.id = s.campus_id
                                                    ORDER BY cl.name ASC, c.name ASC";
                                            $res = $mysqli->query($sql);
                                            if ($res && $res->num_rows > 0) {
                                                while($row = $res->fetch_assoc()){
                                                    echo "<tr>";
                                                    echo "<td>".htmlspecialchars($row['campus_name'])."</td>";
                                                    echo "<td>".htmlspecialchars($row['consumable_name'])."</td>";
                                                    echo "<td>".htmlspecialchars($row['category'])."</td>";
                                                    echo "<td>".intval($row['quantity'])."</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='4' class='text-center text-muted'>No scan stock records found.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div> <!-- container -->

                </div> <!-- content -->

            </div>
            <!-- End Page content -->

        </div>
        <!-- END wrapper -->

    </body>
</html>
