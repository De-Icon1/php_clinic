<?php
session_start();
include('assets/inc/config.php');

// Provide CSV format template for pharmacy consumables import
if (isset($_GET['download_format'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="pharmacy_stock_format.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['name','category','campus_name','quantity','supplier','lpo']);
    fputcsv($out, ['Paracetamol 500mg','Drug','Main Store','100','Acme Pharma','LPO-001']);
    fclose($out);
    exit();
}

// Ensure pharmacy_consumable_stock has supplier_name and lpo_ref columns
$col1 = $mysqli->query("SHOW COLUMNS FROM pharmacy_consumable_stock LIKE 'supplier_name'");
if (!$col1 || $col1->num_rows == 0) {
    @ $mysqli->query("ALTER TABLE pharmacy_consumable_stock ADD COLUMN supplier_name VARCHAR(255) DEFAULT NULL");
}
$col2 = $mysqli->query("SHOW COLUMNS FROM pharmacy_consumable_stock LIKE 'lpo_ref'");
if (!$col2 || $col2->num_rows == 0) {
    @ $mysqli->query("ALTER TABLE pharmacy_consumable_stock ADD COLUMN lpo_ref VARCHAR(100) DEFAULT NULL");
}

/* ===============================
   DELETE STOCK
=============================== */
if(isset($_GET['del_stock'])){
    $id = $_GET['del_stock'];
    $del = $mysqli->prepare("DELETE FROM pharmacy_consumable_stock WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();
    if($del){
        $success = "Consumable stock deleted successfully";
    } else {
        $err = "Unable to delete stock. Try again.";
    }
}

/* ===============================
   DELETE CONSUMABLE TYPE
=============================== */
if(isset($_GET['del_item'])){
    $id = $_GET['del_item'];
    $del = $mysqli->prepare("DELETE FROM pharmacy_consumables WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();
    if($del){
        $success = "Consumable type deleted successfully";
    } else {
        $err = "Unable to delete item. Try again.";
    }
}

/* ===============================
   ADD CONSUMABLE TYPE
=============================== */
if(isset($_POST['add_consumable'])){
    $name = $_POST['name'];
    $category = $_POST['category'];
    $stmt = $mysqli->prepare("INSERT INTO pharmacy_consumables (name, category) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $category);
    $stmt->execute();
    if($stmt){
        $success = "Consumable Registered Successfully";
    } else {
        $err = "Please Try Again";
    }
}

/* ===============================
   ADD STOCK
=============================== */
if(isset($_POST['add_stock'])){
    $consumable_id = $_POST['consumable_id'];
    $campus_name = $_POST['location'];
    $quantity = $_POST['quantity'];
    $supplier = isset($_POST['supplier']) ? trim($_POST['supplier']) : null;
    $lpo = isset($_POST['lpo']) ? trim($_POST['lpo']) : null;

    // Get campus_id
    $campus = $mysqli->prepare("SELECT id FROM campus_locations WHERE name=?");
    $campus->bind_param("s", $campus_name);
    $campus->execute();
    $res = $campus->get_result()->fetch_assoc();
    $campus_id = $res['id'] ?? null;

    if(!$campus_id){
        $err = "Selected campus does not exist";
    } else {
        // Check if stock exists
        $check = $mysqli->prepare("SELECT id FROM pharmacy_consumable_stock WHERE consumable_id=? AND campus_id=?");
        $check->bind_param("ii", $consumable_id, $campus_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();

        if($row){
            // update quantity and optionally supplier/LPO
            $update = $mysqli->prepare("UPDATE pharmacy_consumable_stock SET quantity = quantity + ?, supplier_name = ?, lpo_ref = ? WHERE id = ?");
            if ($update) {
                $update->bind_param("issi", $quantity, $supplier, $lpo, $row['id']);
                $update->execute();
            }
        } else {
            $insert = $mysqli->prepare("INSERT INTO pharmacy_consumable_stock (consumable_id, campus_id, quantity, supplier_name, lpo_ref) VALUES (?, ?, ?, ?, ?)");
            if ($insert) {
                $insert->bind_param("iiiss", $consumable_id, $campus_id, $quantity, $supplier, $lpo);
                $insert->execute();
            }
        }

        if($insert || $update){
            $success = "Stock Added Successfully";
        } else {
            $err = "Unable to add stock";
        }
    }
}

/* ===============================
   TRANSFER STOCK
=============================== */
if(isset($_POST['transfer_stock'])){
    $consumable_id = $_POST['consumable_id'];
    $from_name = $_POST['from_campus'];
    $to_name = $_POST['to_campus'];
    $quantity = (int)$_POST['quantity'];

    // Get campus IDs
    $campus = $mysqli->prepare("SELECT id FROM campus_locations WHERE name=?");

    $campus->bind_param("s", $from_name);
    $campus->execute();
    $from_id = $campus->get_result()->fetch_assoc()['id'] ?? null;

    $campus->bind_param("s", $to_name);
    $campus->execute();
    $to_id = $campus->get_result()->fetch_assoc()['id'] ?? null;

    if(!$from_id || !$to_id){
        $err = "Invalid campus selection.";
    } elseif($from_id == $to_id){
        $err = "Source and destination cannot be the same.";
    } else {
        // Check available stock at source
        $check = $mysqli->prepare("SELECT quantity, id FROM pharmacy_consumable_stock WHERE consumable_id=? AND campus_id=?");
        $check->bind_param("ii", $consumable_id, $from_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();

        if(!$row || $row['quantity'] < $quantity){
            $err = "Not enough stock in source campus.";
        } else {
            // Deduct from source
            $update_from = $mysqli->prepare("UPDATE pharmacy_consumable_stock SET quantity = quantity - ? WHERE id=?");
            $update_from->bind_param("ii", $quantity, $row['id']);
            $update_from->execute();

            // Determine destination unit/table
            $to_unit = isset($_POST['to_unit']) ? trim($_POST['to_unit']) : 'pharmacy';

            if ($to_unit === 'pharmacy') {
                // Add to destination pharmacy stock
                $check_dest = $mysqli->prepare("SELECT id FROM pharmacy_consumable_stock WHERE consumable_id=? AND campus_id=?");
                $check_dest->bind_param("ii", $consumable_id, $to_id);
                $check_dest->execute();
                $dest_row = $check_dest->get_result()->fetch_assoc();

                if($dest_row){
                    $update_to = $mysqli->prepare("UPDATE pharmacy_consumable_stock SET quantity = quantity + ? WHERE id=?");
                    $update_to->bind_param("ii", $quantity, $dest_row['id']);
                    $update_to->execute();
                } else {
                    $insert_to = $mysqli->prepare("INSERT INTO pharmacy_consumable_stock (consumable_id, campus_id, quantity) VALUES (?,?,?)");
                    $insert_to->bind_param("iii", $consumable_id, $to_id, $quantity);
                    $insert_to->execute();
                }

            } elseif ($to_unit === 'nursing') {
                // Map to nurse_consumables / nurse_consumable_stock
                $stmt = $mysqli->prepare("SELECT name, category FROM pharmacy_consumables WHERE id = ? LIMIT 1");
                $stmt->bind_param('i', $consumable_id);
                $stmt->execute();
                $p = $stmt->get_result()->fetch_assoc();
                $name = $p['name'] ?? '';
                $category = $p['category'] ?? 'Pharmacy';

                // find or create nurse_consumables entry
                $nc_stmt = $mysqli->prepare("SELECT id FROM nurse_consumables WHERE name=? LIMIT 1");
                $nc_stmt->bind_param('s', $name);
                $nc_stmt->execute();
                $nc_res = $nc_stmt->get_result();
                if ($nc_res && $nc_res->num_rows) {
                    $cons_id = $nc_res->fetch_assoc()['id'];
                } else {
                    $ins_nc = $mysqli->prepare("INSERT INTO nurse_consumables (name, category) VALUES (?, ?)");
                    $ins_nc->bind_param('ss', $name, $category);
                    $ins_nc->execute();
                    $cons_id = $ins_nc->insert_id ?: 0;
                }

                // add/update nurse stock at destination campus
                $check_dest = $mysqli->prepare("SELECT id, quantity FROM nurse_consumable_stock WHERE consumable_id=? AND campus_id=? LIMIT 1");
                $check_dest->bind_param('ii', $cons_id, $to_id);
                $check_dest->execute();
                $dest_row = $check_dest->get_result()->fetch_assoc();
                if ($dest_row) {
                    $new_qty = intval($dest_row['quantity']) + $quantity;
                    $update_to = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity = ? WHERE id=?");
                    $update_to->bind_param('ii', $new_qty, $dest_row['id']);
                    $update_to->execute();
                } else {
                    $insert_to = $mysqli->prepare("INSERT INTO nurse_consumable_stock (consumable_id, campus_id, quantity) VALUES (?,?,?)");
                    $insert_to->bind_param('iii', $cons_id, $to_id, $quantity);
                    $insert_to->execute();
                }

            } elseif ($to_unit === 'lab') {
                // Map to lab_consumables / lab_consumable_stock
                $stmt = $mysqli->prepare("SELECT name, category FROM pharmacy_consumables WHERE id = ? LIMIT 1");
                $stmt->bind_param('i', $consumable_id);
                $stmt->execute();
                $p = $stmt->get_result()->fetch_assoc();
                $name = $p['name'] ?? '';
                $category = $p['category'] ?? 'Pharmacy';

                $lc_stmt = $mysqli->prepare("SELECT id FROM lab_consumables WHERE name=? LIMIT 1");
                $lc_stmt->bind_param('s', $name);
                $lc_stmt->execute();
                $lc_res = $lc_stmt->get_result();
                if ($lc_res && $lc_res->num_rows) {
                    $cons_id = $lc_res->fetch_assoc()['id'];
                } else {
                    $ins_lc = $mysqli->prepare("INSERT INTO lab_consumables (name, category) VALUES (?, ?)");
                    $ins_lc->bind_param('ss', $name, $category);
                    $ins_lc->execute();
                    $cons_id = $ins_lc->insert_id ?: 0;
                }

                $check_dest = $mysqli->prepare("SELECT id, quantity FROM lab_consumable_stock WHERE consumable_id=? AND campus_id=? LIMIT 1");
                $check_dest->bind_param('ii', $cons_id, $to_id);
                $check_dest->execute();
                $dest_row = $check_dest->get_result()->fetch_assoc();
                if ($dest_row) {
                    $new_qty = intval($dest_row['quantity']) + $quantity;
                    $update_to = $mysqli->prepare("UPDATE lab_consumable_stock SET quantity = ? WHERE id=?");
                    $update_to->bind_param('ii', $new_qty, $dest_row['id']);
                    $update_to->execute();
                } else {
                    $insert_to = $mysqli->prepare("INSERT INTO lab_consumable_stock (consumable_id, campus_id, quantity) VALUES (?,?,?)");
                    $insert_to->bind_param('iii', $cons_id, $to_id, $quantity);
                    $insert_to->execute();
                }

            } elseif ($to_unit === 'scan') {
                // Map to scan_consumables / scan_consumable_stock
                $stmt = $mysqli->prepare("SELECT name, category FROM pharmacy_consumables WHERE id = ? LIMIT 1");
                $stmt->bind_param('i', $consumable_id);
                $stmt->execute();
                $p = $stmt->get_result()->fetch_assoc();
                $name = $p['name'] ?? '';
                $category = $p['category'] ?? 'Pharmacy';

                $sc_stmt = $mysqli->prepare("SELECT id FROM scan_consumables WHERE name=? LIMIT 1");
                $sc_stmt->bind_param('s', $name);
                $sc_stmt->execute();
                $sc_res = $sc_stmt->get_result();
                if ($sc_res && $sc_res->num_rows) {
                    $cons_id = $sc_res->fetch_assoc()['id'];
                } else {
                    $ins_sc = $mysqli->prepare("INSERT INTO scan_consumables (name, category) VALUES (?, ?)");
                    $ins_sc->bind_param('ss', $name, $category);
                    $ins_sc->execute();
                    $cons_id = $ins_sc->insert_id ?: 0;
                }

                $check_dest = $mysqli->prepare("SELECT id, quantity FROM scan_consumable_stock WHERE consumable_id=? AND campus_id=? LIMIT 1");
                $check_dest->bind_param('ii', $cons_id, $to_id);
                $check_dest->execute();
                $dest_row = $check_dest->get_result()->fetch_assoc();
                if ($dest_row) {
                    $new_qty = intval($dest_row['quantity']) + $quantity;
                    $update_to = $mysqli->prepare("UPDATE scan_consumable_stock SET quantity = ? WHERE id=?");
                    $update_to->bind_param('ii', $new_qty, $dest_row['id']);
                    $update_to->execute();
                } else {
                    $insert_to = $mysqli->prepare("INSERT INTO scan_consumable_stock (consumable_id, campus_id, quantity) VALUES (?,?,?)");
                    $insert_to->bind_param('iii', $cons_id, $to_id, $quantity);
                    $insert_to->execute();
                }
            }

            $success = "Stock transferred successfully.";
        }
    }
}

/* ===============================
   CSV EXPORT
=============================== */
if(isset($_GET['export_stock'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="pharmacy_stock.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Consumable Name','Category','Campus','Quantity','Supplier','Reference/LPO']);

    $sql = "SELECT s.quantity, cl.name AS campus_name, c.name AS consumable_name, c.category
            FROM pharmacy_consumable_stock s
            JOIN pharmacy_consumables c ON c.id = s.consumable_id
            JOIN campus_locations cl ON cl.id = s.campus_id
            ORDER BY cl.name ASC, c.name ASC";
    $res = $mysqli->query($sql);
    while($row = $res->fetch_assoc()){
        fputcsv($output, [$row['consumable_name'], $row['category'], $row['campus_name'], $row['quantity'], $row['supplier_name'] ?? '', $row['lpo_ref'] ?? '']);
    }
    fclose($output);
    exit();
}

/* ===============================
   CSV IMPORT
=============================== */
if(isset($_POST['import_stock'])){
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

            // Get consumable_id
            $stmt = $mysqli->prepare("SELECT id FROM pharmacy_consumables WHERE name=? AND category=?");
            $stmt->bind_param("ss", $name, $category);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $consumable_id = $res['id'] ?? null;
            if(!$consumable_id) continue;

            // Get campus_id
            $campus = $mysqli->prepare("SELECT id FROM campus_locations WHERE name=?");
            $campus->bind_param("s", $campus_name);
            $campus->execute();
            $res = $campus->get_result()->fetch_assoc();
            $campus_id = $res['id'] ?? null;
            if(!$campus_id) continue;

            // Check stock
            $check = $mysqli->prepare("SELECT id FROM pharmacy_consumable_stock WHERE consumable_id=? AND campus_id=?");
            $check->bind_param("ii", $consumable_id, $campus_id);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();

            if($existing){
                $update = $mysqli->prepare("UPDATE pharmacy_consumable_stock SET quantity=?, supplier_name=?, lpo_ref=? WHERE id=?");
                $update->bind_param("issi", $quantity, $supplier, $lpo, $existing['id']);
                $update->execute();
            } else {
                $insert = $mysqli->prepare("INSERT INTO pharmacy_consumable_stock (consumable_id, campus_id, quantity, supplier_name, lpo_ref) VALUES (?,?,?,?,?)");
                $insert->bind_param("iiiss", $consumable_id, $campus_id, $quantity, $supplier, $lpo);
                $insert->execute();
            }
        }
        fclose($handle);
        $success = "Stock imported successfully";
    }
}

/* ===============================
   ADD CAMPUS LOCATION
=============================== */
if(isset($_POST['add_campus'])){
    $campus_name = $_POST['campus_name'];

    $stmt = $mysqli->prepare("INSERT INTO campus_locations (name) VALUES (?)");
    $stmt->bind_param("s", $campus_name);
    $stmt->execute();

    if($stmt){
        $success = "Campus location added successfully";
    } else {
        $err = "Unable to add campus. Try again.";
    }
}

/* ===============================
   DELETE CAMPUS LOCATION
=============================== */
if(isset($_GET['del_campus'])){
    $id = $_GET['del_campus'];
    $del = $mysqli->prepare("DELETE FROM campus_locations WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();
    if($del){
        $success = "Campus deleted successfully";
    } else {
        $err = "Unable to delete campus. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('assets/inc/head.php'); ?>
<body>
<div id="wrapper">
    <?php include("assets/inc/nav_r.php"); ?>
    <?php include("assets/inc/sidebar_admin.php"); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <h4 class="page-title">Pharmacy Consumables Management</h4>
                        </div>
                    </div>
                </div>

                <!-- Add Consumable -->
                <div class="card">
                    <div class="card-body">
                        <h4>Add New Consumable</h4>
                        <form method="post">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Consumable Name</label>
                                    <input type="text" required name="name" class="form-control" placeholder="e.g. Paracetamol 500mg">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Category</label>
                                    <input type="text" required name="category" class="form-control" placeholder="e.g. Tablets">
                                </div>
                            </div>
                            <button type="submit" name="add_consumable" class="btn btn-primary">Add Consumable</button>
                        </form>
                    </div>
                </div>

                <hr>

                <!-- Add Stock -->
                <div class="card">
                    <div class="card-body">
                        <h4>Add Stock to Centre</h4>
                        <form method="post">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Select Consumable</label>
                                    <select name="consumable_id" required class="form-control">
                                        <option value="">Choose</option>
                                        <?php
                                        $q = $mysqli->query("SELECT * FROM pharmacy_consumables ORDER BY name ASC");
                                        while($r = $q->fetch_assoc()){
                                            echo "<option value='{$r['id']}'>{$r['name']} - {$r['category']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Campus</label>
                                    <select name="location" class="form-control" required>
                                        <?php
                                        $campuses = $mysqli->query("SELECT name FROM campus_locations ORDER BY name ASC");
                                        while($c = $campuses->fetch_assoc()){
                                            echo "<option>{$c['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Quantity</label>
                                    <input type="number" required name="quantity" class="form-control">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Supplier Name</label>
                                    <input type="text" name="supplier" class="form-control" placeholder="Supplier (optional)">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Reference / LPO</label>
                                    <input type="text" name="lpo" class="form-control" placeholder="Ref / LPO (optional)">
                                </div>
                            </div>
                            <button type="submit" name="add_stock" class="btn btn-success">Add Stock</button>
                        </form>
                    </div>
                </div>

                <hr>

                <!-- Transfer Stock -->
                <div class="card">
                    <div class="card-body">
                        <h4>Transfer Stock Between Campuses / Units</h4>
                        <form method="post">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Consumable</label>
                                    <select name="consumable_id" required class="form-control">
                                        <option value="">Choose</option>
                                        <?php
                                        $q->data_seek(0);
                                        while($r = $q->fetch_assoc()){
                                            echo "<option value='{$r['id']}'>{$r['name']} - {$r['category']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>From Campus</label>
                                    <select name="from_campus" class="form-control" required>
                                        <?php
                                        $campuses->data_seek(0);
                                        while($c = $campuses->fetch_assoc()){
                                            echo "<option>{$c['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>To Campus / Unit</label>
                                    <select name="to_campus" class="form-control" required>
                                        <?php
                                        $campuses->data_seek(0);
                                        while($c = $campuses->fetch_assoc()){
                                            echo "<option>{$c['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>To Unit</label>
                                    <select name="to_unit" class="form-control">
                                        <option value="pharmacy">Pharmacy</option>
                                        <option value="nursing">Nursing</option>
                                        <option value="lab">Laboratory</option>
                                        <option value="scan">Scan</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Quantity</label>
                                    <input type="number" name="quantity" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" name="transfer_stock" class="btn btn-warning">Transfer Stock</button>
                        </form>
                    </div>
                </div>

                <hr>

                <!-- CSV Import / Export -->
                <div class="mb-2">
                    <form method="post" enctype="multipart/form-data" class="d-inline">
                        <input type="file" name="csv_file" required>
                        <button type="submit" name="import_stock" class="btn btn-success">Import Stock CSV</button>
                    </form>
                    <a href="pharmacy_consumables.php?export_stock=1" class="btn btn-info">Export Stock CSV</a>
                </div>

                <!-- List Consumables -->
                <div class="card">
                    <div class="card-body">
