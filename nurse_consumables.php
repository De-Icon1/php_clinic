<?php
session_start();
include('assets/inc/config.php');

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
   1. REGISTER NEW NURSE CONSUMABLE
================================================== */
if (isset($_POST['add_consumable'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];

    $stmt = $mysqli->prepare("INSERT INTO nurse_consumables (name, category) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $category);
    $stmt->execute();
    if ($stmt) $success = "Consumable added successfully!";
    else $err = "Failed to add consumable.";
}

/* ==================================================
   2. ADD STOCK TO MAIN STORE
================================================== */
if (isset($_POST['add_stock'])) {
    $consumable_id = $_POST['consumable_id'];
    $qty = (int)$_POST['qty'];

    $chk = $mysqli->prepare("SELECT id, quantity FROM nurse_consumable_stock WHERE consumable_id=? AND campus_id=?");
    $chk->bind_param("ii", $consumable_id, $main_store_id);
    $chk->execute();
    $r = $chk->get_result()->fetch_assoc();

    if ($r) {
        $new_qty = $r['quantity'] + $qty;
        $upd = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity=? WHERE id=?");
        $upd->bind_param("ii", $new_qty, $r['id']);
        $upd->execute();
    } else {
        $ins = $mysqli->prepare("INSERT INTO nurse_consumable_stock (consumable_id, campus_id, quantity) VALUES (?, ?, ?)");
        $ins->bind_param("iii", $consumable_id, $main_store_id, $qty);
        $ins->execute();
    }
    $success = "Stock added to Main Store!";
}

/* ==================================================
   3. TRANSFER STOCK (ISSUE / RETURN)
================================================== */
if (isset($_POST['transfer'])) {
    $consumable_id = $_POST['consumable_id'];
    $campus_id     = $_POST['campus_id'];
    $qty           = (int)$_POST['qty'];
    $action        = $_POST['action'];

    $main_stock = $mysqli->query("SELECT quantity FROM nurse_consumable_stock WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
    $main_qty = ($main_stock->num_rows) ? $main_stock->fetch_assoc()['quantity'] : 0;

    $camp_stock = $mysqli->query("SELECT quantity FROM nurse_consumable_stock WHERE consumable_id=$consumable_id AND campus_id=$campus_id");
    $camp_qty = ($camp_stock->num_rows) ? $camp_stock->fetch_assoc()['quantity'] : 0;

    if ($action == "issue") {
        if ($main_qty < $qty) $err = "Not enough stock in Main Store!";
        else {
            $mysqli->query("UPDATE nurse_consumable_stock SET quantity=quantity-$qty WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
            $mysqli->query("INSERT INTO nurse_consumable_stock (consumable_id, campus_id, quantity) VALUES ($consumable_id, $campus_id, $qty) ON DUPLICATE KEY UPDATE quantity=quantity+$qty");
            $success = "Issued successfully!";
        }
    } elseif ($action == "return") {
        if ($camp_qty < $qty) $err = "Campus does not have enough stock!";
        else {
            $mysqli->query("UPDATE nurse_consumable_stock SET quantity=quantity-$qty WHERE consumable_id=$consumable_id AND campus_id=$campus_id");
            $mysqli->query("UPDATE nurse_consumable_stock SET quantity=quantity+$qty WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
            $success = "Returned successfully!";
        }
    }
}

/* ==================================================
   4. QUICK ISSUE / RETURN MODAL
================================================== */
if (isset($_POST['nurse_quick_issue'])) {
    $consumable_id = $_POST['consumable_id'];
    $campus_id     = $_POST['campus_id'];
    $qty           = (int)$_POST['qty'];
    $action        = $_POST['action_type'];

    $main_stock = $mysqli->query("SELECT quantity FROM nurse_consumable_stock WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
    $main_qty = ($main_stock->num_rows) ? $main_stock->fetch_assoc()['quantity'] : 0;

    $camp_stock = $mysqli->query("SELECT quantity FROM nurse_consumable_stock WHERE consumable_id=$consumable_id AND campus_id=$campus_id");
    $camp_qty = ($camp_stock->num_rows) ? $camp_stock->fetch_assoc()['quantity'] : 0;

    if ($action == "issue") {
        if ($main_qty < $qty) $err = "Not enough stock in Main Store!";
        else {
            $mysqli->query("UPDATE nurse_consumable_stock SET quantity=quantity-$qty WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
            $mysqli->query("INSERT INTO nurse_consumable_stock (consumable_id, campus_id, quantity) VALUES ($consumable_id, $campus_id, $qty) ON DUPLICATE KEY UPDATE quantity=quantity+$qty");
            $success = "Issued successfully!";
        }
    } elseif ($action == "return") {
        if ($camp_qty < $qty) $err = "Not enough stock at campus!";
        else {
            $mysqli->query("UPDATE nurse_consumable_stock SET quantity=quantity-$qty WHERE consumable_id=$consumable_id AND campus_id=$campus_id");
            $mysqli->query("UPDATE nurse_consumable_stock SET quantity=quantity+$qty WHERE consumable_id=$consumable_id AND campus_id=$main_store_id");
            $success = "Returned successfully!";
        }
    }
}

/* ==================================================
   CSV EXPORT / IMPORT
================================================== */
if(isset($_GET['export_nurse_stock'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="nurse_stock.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Consumable Name','Category','Campus','Quantity']);
    $sql = "SELECT s.quantity, cl.name AS campus_name, n.name AS consumable_name, n.category
            FROM nurse_consumable_stock s
            JOIN nurse_consumables n ON n.id = s.consumable_id
            JOIN campus_locations cl ON cl.id = s.campus_id
            ORDER BY cl.name ASC, n.name ASC";
    $res = $mysqli->query($sql);
    while($row = $res->fetch_assoc()) fputcsv($output, [$row['consumable_name'], $row['category'], $row['campus_name'], $row['quantity']]);
    fclose($output);
    exit();
}

if(isset($_POST['import_nurse_stock'])){
    $file = $_FILES['csv_file']['tmp_name'];
    if(($handle = fopen($file, "r")) !== FALSE){
        $row = 0;
        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
            if($row++ == 0) continue; // skip header
            list($name, $category, $campus_name, $quantity) = $data;
            $stmt = $mysqli->prepare("SELECT id FROM nurse_consumables WHERE name=? AND category=?");
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
            $check = $mysqli->prepare("SELECT id FROM nurse_consumable_stock WHERE consumable_id=? AND campus_id=?");
            $check->bind_param("ii", $consumable_id, $campus_id);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();
            if($existing){
                $update = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity=? WHERE id=?");
                $update->bind_param("ii", $quantity, $existing['id']);
                $update->execute();
            } else {
                $insert = $mysqli->prepare("INSERT INTO nurse_consumable_stock (consumable_id, campus_id, quantity) VALUES (?,?,?)");
                $insert->bind_param("iii", $consumable_id, $campus_id, $quantity);
                $insert->execute();
            }
        }
        fclose($handle);
        $success = "Stock imported successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nursing Consumables Store</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light p-3">
<div class="container">
    <h3 class="mb-3">Nursing Consumables Store</h3>

    <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if(isset($err)) echo "<div class='alert alert-danger'>$err</div>"; ?>

    <!-- CSV Import / Export -->
    <div class="mb-3">
        <form method="post" enctype="multipart/form-data" class="d-inline">
            <input type="file" name="csv_file" required>
            <button type="submit" name="import_nurse_stock" class="btn btn-success">Import Nurse Stock CSV</button>
        </form>
        <a href="nurse_consumables.php?export_nurse_stock=1" class="btn btn-info">Export Nurse Stock CSV</a>
    </div>

    <!-- Register Consumable -->
    <div class="card mb-3">
        <div class="card-header">Register Consumable</div>
        <div class="card-body">
            <form method="post" class="row g-2">
                <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                <div class="col-md-4"><input type="text" name="category" class="form-control" placeholder="Category" required></div>
                <div class="col-md-4"><button class="btn btn-primary" name="add_consumable">Add</button></div>
            </form>
        </div>
    </div>

    <!-- Add Stock to Main Store -->
    <div class="card mb-3">
        <div class="card-header">Add Stock to Main Store</div>
        <div class="card-body">
            <form method="post" class="row g-2">
                <div class="col-md-4">
                    <select name="consumable_id" class="form-control" required>
                        <option value="">-- Select Consumable --</option>
                        <?php
                        $q = $mysqli->query("SELECT * FROM nurse_consumables ORDER BY name ASC");
                        while($r=$q->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['name']} ({$r['category']})</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-4"><input type="number" name="qty" min="1" class="form-control" placeholder="Quantity" required></div>
                <div class="col-md-4"><button class="btn btn-success" name="add_stock">Add Stock</button></div>
            </form>
        </div>
    </div>

    <!-- Transfer Stock -->
    <div class="card mb-3">
        <div class="card-header">Transfer Stock (Issue / Return)</div>
        <div class="card-body">
            <form method="post" class="row g-2">
                <div class="col-md-4">
                    <select name="consumable_id" class="form-control" required>
                        <?php
                        $q = $mysqli->query("SELECT * FROM nurse_consumables ORDER BY name ASC");
                        while($r=$q->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['name']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="campus_id" class="form-control" required>
                        <?php
                        foreach($campuses as $name=>$id){
                            if($name=="Main Store") continue;
                            echo "<option value='$id'>$name</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4"><input type="number" name="qty" class="form-control" min="1" placeholder="Quantity"></div>
                <div class="col-md-4 mt-2">
                    <select name="action" class="form-control">
                        <option value="issue">Issue (Main → Campus)</option>
                        <option value="return">Return (Campus → Main)</option>
                    </select>
                </div>
                <div class="col-md-4 mt-2"><button class="btn btn-primary" name="transfer">Process</button></div>
            </form>
        </div>
    </div>

    <!-- Quick Issue / Return Modal Trigger -->
    <button class="btn btn-warning mb-3" data-toggle="modal" data-target="#quickIssueModal">Quick Issue / Return</button>

    <!-- Main Store Inventory -->
    <h4>Main Store Inventory</h4>
    <table class="table table-bordered">
        <thead><tr><th>Consumable</th><th>Qty</th></tr></thead>
        <tbody>
            <?php
            $q = $mysqli->query("SELECT n.name, n.category, s.quantity FROM nurse_consumables n LEFT JOIN nurse_consumable_stock s ON n.id=s.consumable_id AND s.campus_id=$main_store_id ORDER BY n.name ASC");
            while($r=$q->fetch_assoc()){
                $qty = $r['quantity'] ?? 0;
                echo "<tr><td>{$r['name']} ({$r['category']})</td><td>{$qty}</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Campus Inventories -->
    <?php
    foreach($campuses as $name=>$id){
        if($name=="Main Store") continue;
        echo "<h4>$name Inventory</h4><table class='table table-bordered'><thead><tr><th>Consumable</th><th>Qty</th></tr></thead><tbody>";
        $q = $mysqli->query("SELECT n.name, n.category, s.quantity FROM nurse_consumables n LEFT JOIN nurse_consumable_stock s ON n.id=s.consumable_id AND s.campus_id=$id ORDER BY n.name ASC");
        while($r=$q->fetch_assoc()){
            $qty = $r['quantity'] ?? 0;
            echo "<tr><td>{$r['name']} ({$r['category']})</td><td>{$qty}</td></tr>";
        }
        echo "</tbody></table>";
    }
    ?>
</div>

<!-- Quick Issue / Return Modal -->
<div class="modal fade" id="quickIssueModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Quick Issue / Return</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <label>Consumable</label>
                    <select name="consumable_id" class="form-control" required>
                        <?php
                        $q = $mysqli->query("SELECT * FROM nurse_consumables ORDER BY name ASC");
                        while($r=$q->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['name']}</option>";
                        ?>
                    </select>
                    <label class="mt-2">Campus</label>
                    <select name="campus_id" class="form-control" required>
                        <?php
                        foreach($campuses as $name=>$id){
                            if($name=="Main Store") continue;
                            echo "<option value='$id'>$name</option>";
                        }
                        ?>
                    </select>
                    <label class="mt-2">Quantity</label>
                    <input type="number" name="qty" class="form-control" required>
                    <label class="mt-2">Action</label>
                    <select name="action_type" class="form-control">
                        <option value="issue">Issue (Main → Campus)</option>
                        <option value="return">Return (Campus → Main)</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" name="nurse_quick_issue">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>