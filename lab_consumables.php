<?php
session_start();
include('assets/inc/config.php');

// Provide CSV format template for lab consumables import
if (isset($_GET['download_format'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="lab_stock_format.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['name','category','campus_name','quantity','supplier','lpo']);
    fputcsv($out, ['Gloves','Consumable','Main Store','100','Acme Supplies','LPO-001']);
    fclose($out);
    exit();
}

// Ensure lab_consumable_stock has supplier_name and lpo_ref columns
$col1 = $mysqli->query("SHOW COLUMNS FROM lab_consumable_stock LIKE 'supplier_name'");
if (!$col1 || $col1->num_rows == 0) {
    @ $mysqli->query("ALTER TABLE lab_consumable_stock ADD COLUMN supplier_name VARCHAR(255) DEFAULT NULL");
}
$col2 = $mysqli->query("SHOW COLUMNS FROM lab_consumable_stock LIKE 'lpo_ref'");
if (!$col2 || $col2->num_rows == 0) {
    @ $mysqli->query("ALTER TABLE lab_consumable_stock ADD COLUMN lpo_ref VARCHAR(100) DEFAULT NULL");
}

/* ===============================
   DELETE STOCK
=============================== */
if(isset($_GET['del_stock'])){
    $id = $_GET['del_stock'];
    $del = $mysqli->prepare("DELETE FROM lab_consumable_stock WHERE id=?");
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
    $del = $mysqli->prepare("DELETE FROM lab_consumables WHERE id=?");
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
    $stmt = $mysqli->prepare("INSERT INTO lab_consumables (name, category) VALUES (?, ?)");
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
        $check = $mysqli->prepare("SELECT id FROM lab_consumable_stock WHERE consumable_id=? AND campus_id=?");
        $check->bind_param("ii", $consumable_id, $campus_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();

        if($row){
            // update quantity and optionally supplier/LPO
            $update = $mysqli->prepare("UPDATE lab_consumable_stock SET quantity = quantity + ?, supplier_name = ?, lpo_ref = ? WHERE id = ?");
            if ($update) {
                $update->bind_param("issi", $quantity, $supplier, $lpo, $row['id']);
                $update->execute();
            }
        } else {
            $insert = $mysqli->prepare("INSERT INTO lab_consumable_stock (consumable_id, campus_id, quantity, supplier_name, lpo_ref) VALUES (?, ?, ?, ?, ?)");
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
        $check = $mysqli->prepare("SELECT quantity, id FROM lab_consumable_stock WHERE consumable_id=? AND campus_id=?");
        $check->bind_param("ii", $consumable_id, $from_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();

        if(!$row || $row['quantity'] < $quantity){
            $err = "Not enough stock in source campus.";
        } else {
            // Deduct from source
            $update_from = $mysqli->prepare("UPDATE lab_consumable_stock SET quantity = quantity - ? WHERE id=?");
            $update_from->bind_param("ii", $quantity, $row['id']);
            $update_from->execute();

            // Add to destination
            $check_dest = $mysqli->prepare("SELECT id FROM lab_consumable_stock WHERE consumable_id=? AND campus_id=?");
            $check_dest->bind_param("ii", $consumable_id, $to_id);
            $check_dest->execute();
            $dest_row = $check_dest->get_result()->fetch_assoc();

            if($dest_row){
                $update_to = $mysqli->prepare("UPDATE lab_consumable_stock SET quantity = quantity + ? WHERE id=?");
                $update_to->bind_param("ii", $quantity, $dest_row['id']);
                $update_to->execute();
            } else {
                $insert_to = $mysqli->prepare("INSERT INTO lab_consumable_stock (consumable_id, campus_id, quantity) VALUES (?,?,?)");
                $insert_to->bind_param("iii", $consumable_id, $to_id, $quantity);
                $insert_to->execute();
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
    header('Content-Disposition: attachment; filename="lab_stock.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Consumable Name','Category','Campus','Quantity','Supplier','Reference/LPO']);

    $sql = "SELECT s.quantity, cl.name AS campus_name, c.name AS consumable_name, c.category
            FROM lab_consumable_stock s
            JOIN lab_consumables c ON c.id = s.consumable_id
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
            $stmt = $mysqli->prepare("SELECT id FROM lab_consumables WHERE name=? AND category=?");
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
            $check = $mysqli->prepare("SELECT id FROM lab_consumable_stock WHERE consumable_id=? AND campus_id=?");
            $check->bind_param("ii", $consumable_id, $campus_id);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();

            if($existing){
                $update = $mysqli->prepare("UPDATE lab_consumable_stock SET quantity=?, supplier_name=?, lpo_ref=? WHERE id=?");
                $update->bind_param("issi", $quantity, $supplier, $lpo, $existing['id']);
                $update->execute();
            } else {
                $insert = $mysqli->prepare("INSERT INTO lab_consumable_stock (consumable_id, campus_id, quantity, supplier_name, lpo_ref) VALUES (?,?,?,?,?)");
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
                            <h4 class="page-title">Laboratory Consumables Management</h4>
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
                                    <input type="text" required name="name" class="form-control" placeholder="e.g. Plastic Petri Dish">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Category</label>
                                    <input type="text" required name="category" class="form-control" placeholder="e.g. Plastics">
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
                                        $q = $mysqli->query("SELECT * FROM lab_consumables ORDER BY name ASC");
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
                        <h4>Transfer Stock Between Campuses</h4>
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
                                <div class="form-group col-md-4">
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
                                <div class="form-group col-md-4">
                                    <label>To Campus</label>
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
                    <a href="setup_consumables.php?export_stock=1" class="btn btn-info">Export Stock CSV</a>
                </div>

                <!-- List Consumables -->
                <div class="card">
                    <div class="card-body">
                        <h4>Registered Consumables</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID</th><th>Name</th><th>Category</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $ret = $mysqli->query("SELECT * FROM lab_consumables ORDER BY id DESC");
                                while($row = $ret->fetch_assoc()){
                                ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td><?= $row['name']; ?></td>
                                    <td><?= $row['category']; ?></td>
                                    <td>
                                        <a href="setup_consumables.php?del_item=<?= $row['id']; ?>">
                                            <img src="assets/images/del.png" height="20">
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- List Stock -->
                <div class="card">
                    <div class="card-body">
                        <h4>Consumable Stock Per Centre</h4>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Campus</th><th>Item</th><th>Category</th><th>Quantity</th><th>Supplier</th><th>Reference / LPO</th><th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT s.id, s.quantity, s.supplier_name, s.lpo_ref, cl.name AS campus_name, c.name AS consumable_name, c.category
                                        FROM lab_consumable_stock s
                                        JOIN lab_consumables c ON c.id = s.consumable_id
                                        JOIN campus_locations cl ON cl.id = s.campus_id
                                        ORDER BY cl.name ASC, c.name ASC";
                                $stock = $mysqli->query($sql);
                                while($row = $stock->fetch_assoc()){
                                    $low_stock = $row['quantity'] < 10 ? 'style="background-color:#f8d7da;"' : '';
                                ?>
                                <tr <?= $low_stock ?>>
                                    <td><?= $row['campus_name']; ?></td>
                                    <td><?= $row['consumable_name']; ?></td>
                                    <td><?= $row['category']; ?></td>
                                    <td><?= $row['quantity']; ?></td>
                                    <td><?= htmlspecialchars($row['supplier_name'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($row['lpo_ref'] ?? ''); ?></td>
                                    <td>
                                        <a href="setup_consumables.php?del_stock=<?= $row['id']; ?>">
                                            <img src="assets/images/del.png" height="20">
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Compact Campus Summary for Quick Visual Check -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h5>Stock Summary by Campus (for visual check)</h5>
                        <p class="text-muted mb-2">
                            This table shows total quantity per consumable at each campus so you can confirm location-based deductions from the Lab Location screen.
                        </p>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Campus</th>
                                    <th>Consumable</th>
                                    <th>Category</th>
                                    <th class="text-right">Total Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sumSql = "SELECT cl.name AS campus_name, c.name AS consumable_name, c.category,
                                                   SUM(s.quantity) AS total_qty
                                           FROM lab_consumable_stock s
                                           JOIN lab_consumables c ON c.id = s.consumable_id
                                           JOIN campus_locations cl ON cl.id = s.campus_id
                                           GROUP BY cl.id, c.id
                                           ORDER BY cl.name ASC, c.name ASC";
                                $sumRes = $mysqli->query($sumSql);
                                if($sumRes && $sumRes->num_rows > 0){
                                    while($row = $sumRes->fetch_assoc()){
                                        $low = $row['total_qty'] < 10 ? 'style="background-color:#fff3cd;"' : '';
                                ?>
                                    <tr <?= $low ?>>
                                        <td><?= $row['campus_name']; ?></td>
                                        <td><?= $row['consumable_name']; ?></td>
                                        <td><?= $row['category']; ?></td>
                                        <td class="text-right"><?= (int)$row['total_qty']; ?></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No stock records found.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <?php include("assets/inc/footer.php"); ?>
    </div>
</div>

<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>
</body>
</html>