<?php
session_start();
include('assets/inc/config.php');
include('assets/inc/checklogins.php');
check_login();
authorize();



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

/* ==================================================
   ADMIN STOCK LIST EXPORT (includes stock_id and campus_id)
================================================== */
if(isset($_GET['export_admin_stock'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="admin_nurse_stock.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Stock ID','Consumable ID','Consumable Name','Category','Campus ID','Campus Name','Quantity']);
    $sql = "SELECT s.id AS stock_id, n.id AS consumable_id, n.name AS consumable_name, n.category, cl.id AS campus_id, cl.name AS campus_name, s.quantity
            FROM nurse_consumable_stock s
            JOIN nurse_consumables n ON n.id = s.consumable_id
            JOIN campus_locations cl ON cl.id = s.campus_id
            ORDER BY cl.name ASC, n.name ASC";
    $res = $mysqli->query($sql);
    while($row = $res->fetch_assoc()){
        fputcsv($output, [$row['stock_id'],$row['consumable_id'],$row['consumable_name'],$row['category'],$row['campus_id'],$row['campus_name'],$row['quantity']]);
    }
    fclose($output);
    exit();
}

/* ==================================================
   DataTables server-side endpoint for Admin Stock List
   Expects GET params from DataTables (start, length, search[value], order)
================================================== */
if (isset($_GET['stock_list'])) {
    $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 0;
    $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
    $length = isset($_GET['length']) ? (int)$_GET['length'] : 25;
    $search = $_GET['search']['value'] ?? '';

    $cols = [
        's.id','n.id','n.name','n.category','cl.id','cl.name','s.quantity'
    ];

    // total records
    $totalRes = $mysqli->query("SELECT COUNT(*) AS cnt FROM nurse_consumable_stock s");
    $recordsTotal = $totalRes->fetch_assoc()['cnt'] ?? 0;

    // base query
    $where = '';
    $params = [];
    if (!empty($search)) {
        $s = "%".$mysqli->real_escape_string($search)."%";
        $where = " WHERE (n.name LIKE ? OR n.category LIKE ? OR cl.name LIKE ? OR s.id LIKE ? OR n.id LIKE ?) ";
        $params = [$s,$s,$s,$s,$s];
    }

    // count filtered
    if ($where) {
        $countQ = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id=s.consumable_id JOIN campus_locations cl ON cl.id=s.campus_id ".$where);
        if ($countQ) {
            $countQ->bind_param(str_repeat('s', count($params)), ...$params);
            $countQ->execute();
            $cres = $countQ->get_result()->fetch_assoc();
            $recordsFiltered = $cres['cnt'] ?? 0;
        } else { $recordsFiltered = $recordsTotal; }
    } else {
        $recordsFiltered = $recordsTotal;
    }

    // ordering
    $orderSql = '';
    if (isset($_GET['order'][0]['column'])) {
        $ocol = (int)$_GET['order'][0]['column'];
        $odir = $_GET['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
        $ocol = max(0, min(count($cols)-1, $ocol));
        $orderSql = " ORDER BY " . $cols[$ocol] . " " . $odir;
    } else {
        $orderSql = " ORDER BY cl.name ASC, n.name ASC";
    }

    $limitSql = " LIMIT ?, ?";

    $sql = "SELECT s.id AS stock_id, n.id AS consumable_id, n.name AS consumable_name, n.category, cl.id AS campus_id, cl.name AS campus_name, s.quantity
            FROM nurse_consumable_stock s
            JOIN nurse_consumables n ON n.id = s.consumable_id
            JOIN campus_locations cl ON cl.id = s.campus_id" . $where . $orderSql . $limitSql;

    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        // bind params: possible search strings, then start, length
        $types = '';
        $bind_vals = [];
        if ($where) {
            $types .= str_repeat('s', count($params));
            foreach ($params as $v) $bind_vals[] = $v;
        }
        $types .= 'ii';
        $bind_vals[] = $start;
        $bind_vals[] = $length;
        $stmt->bind_param($types, ...$bind_vals);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($r = $res->fetch_assoc()) {
            $data[] = [
                $r['stock_id'], $r['consumable_id'], $r['consumable_name'], $r['category'], $r['campus_id'], $r['campus_name'], (int)$r['quantity'], ''
            ];
        }
    } else {
        $data = [];
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => (int)$recordsTotal,
        'recordsFiltered' => (int)$recordsFiltered,
        'data' => $data
    ]);
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
<html lang="en">

    <!--Head Code-->
    <?php include("assets/inc/head.php");?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start --
            <?php include('assets/inc/nav.php');?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
            <?php include('assets/inc/sidebar.php');?>
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
                                    <h4 class="page-title">Nursing Consumables Store</h4>
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
            <button type="submit" name="import_nurse_stock" class="btn btn-success">Import Nurse Stock CSV</button>
        </form>
        <a href="nurse_consumables.php?export_nurse_stock=1" class="btn btn-info">Export Stock CSV</a>
    </div>

    <!-- Register Consumable -->
    <div class="card">
        <div class="card-body">
            <h4>Register Consumable</h4>
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
    <div class="card">
        <div class="card-body">
            <h4>Add Stock to Main Store</h4>
            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Select Consumable</label>
                        <select name="consumable_id" class="form-control" required>
                            <option value="">-- Select Consumable --</option>
                            <?php
                            $q = $mysqli->query("SELECT * FROM nurse_consumables ORDER BY name ASC");
                            while($r=$q->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['name']} ({$r['category']})</option>";
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Quantity</label>
                        <input type="number" name="qty" min="1" class="form-control" placeholder="Quantity" required>
                    </div>
                </div>
                <button class="btn btn-success" name="add_stock">Add Stock</button>
            </form>
        </div>
    </div>

    <!-- Transfer Stock -->
    <div class="card">
        <div class="card-body">
            <h4>Transfer Stock (Issue / Return)</h4>
            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Consumable</label>
                        <select name="consumable_id" class="form-control" required>
                            <?php
                            $q = $mysqli->query("SELECT * FROM nurse_consumables ORDER BY name ASC");
                            while($r=$q->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['name']}</option>";
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Campus</label>
                        <select name="campus_id" class="form-control" required>
                            <?php
                            foreach($campuses as $name=>$id){
                                if($name=="Main Store") continue;
                                echo "<option value='$id'>$name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Quantity</label>
                        <input type="number" name="qty" class="form-control" min="1" placeholder="Quantity">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Action</label>
                        <select name="action" class="form-control">
                            <option value="issue">Issue (Main → Campus)</option>
                            <option value="return">Return (Campus → Main)</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" name="transfer">Process</button>
            </form>
        </div>
    </div>

    <!-- Quick Issue / Return Modal Trigger -->
    <button class="btn btn-warning mb-3" data-toggle="modal" data-target="#quickIssueModal">Quick Issue / Return</button>

    <!-- Main Store Inventory -->
    <div class="card mt-3">
        <div class="card-body">
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
        </div>
    </div>

    <!-- Campus Inventories -->
    <?php
    foreach($campuses as $name=>$id){
        if($name=="Main Store") continue;
    ?>
    <div class="card mt-3">
        <div class="card-body">
            <h4><?= htmlspecialchars($name); ?> Inventory</h4>
            <table class='table table-bordered'>
                <thead><tr><th>Consumable</th><th>Qty</th></tr></thead>
                <tbody>
                <?php
                $q = $mysqli->query("SELECT n.name, n.category, s.quantity FROM nurse_consumables n LEFT JOIN nurse_consumable_stock s ON n.id=s.consumable_id AND s.campus_id=$id ORDER BY n.name ASC");
                while($r=$q->fetch_assoc()){
                    $qty = $r['quantity'] ?? 0;
                    echo "<tr><td>{$r['name']} ({$r['category']})</td><td>{$qty}</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>

    <!-- Admin Stock List (with Stock ID, Copy button, and DataTables) -->
    <div class="card mt-4">
        <div class="card-body">
            <h4>Admin Stock List</h4>
            <div class="mb-2">
                <a href="nurse_consumables.php?export_admin_stock=1" class="btn btn-sm btn-outline-primary">Export Stock CSV</a>
            </div>
            <div class="table-responsive">
                <table id="adminStockTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Stock ID</th>
                            <th>Consumable ID</th>
                            <th>Consumable Name</th>
                            <th>Category</th>
                            <th>Campus ID</th>
                            <th>Campus Name</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT s.id AS stock_id, n.id AS consumable_id, n.name AS consumable_name, n.category, cl.id AS campus_id, cl.name AS campus_name, s.quantity
                            FROM nurse_consumable_stock s
                            JOIN nurse_consumables n ON n.id = s.consumable_id
                            JOIN campus_locations cl ON cl.id = s.campus_id
                            ORDER BY cl.name ASC, n.name ASC";
                    $res = $mysqli->query($sql);
                    while($row = $res->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($row['stock_id'])."</td>";
                        echo "<td>".htmlspecialchars($row['consumable_id'])."</td>";
                        echo "<td>".htmlspecialchars($row['consumable_name'])."</td>";
                        echo "<td>".htmlspecialchars($row['category'])."</td>";
                        echo "<td>".htmlspecialchars($row['campus_id'])."</td>";
                        echo "<td>".htmlspecialchars($row['campus_name'])."</td>";
                        echo "<td>".((int)$row['quantity'])."</td>";
                        echo "<td><button class='btn btn-sm btn-outline-secondary copy-stock' data-stock='".htmlspecialchars($row['stock_id'])."'>Copy ID</button></td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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

                    <!-- End page title --> 

                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->

        <?php include("assets/inc/footer.php"); ?>

        <!-- DataTables CSS/JS (CDN) -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script>
        jQuery(function($){
            // Initialize DataTable for admin stock list if present
            if ($('#adminStockTable').length) {
                $('#adminStockTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: { url: 'nurse_consumables.php?stock_list=1', type: 'GET' },
                    pageLength: 25,
                    order: [[5, 'asc']],
                    columns: [
                        { data: 0 },
                        { data: 1 },
                        { data: 2 },
                        { data: 3 },
                        { data: 4 },
                        { data: 5 },
                        { data: 6 },
                        { data: null, orderable: false, searchable: false, render: function(data,type,row){ return '<button class="btn btn-sm btn-outline-secondary copy-stock" data-stock="'+row[0]+'">Copy ID</button>'; } }
                    ]
                });
            }

            // Copy Stock ID to clipboard
            $(document).on('click', '.copy-stock', function(e){
                e.preventDefault();
                var $btn = $(this);
                var stock = $btn.data('stock');
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(stock).then(function(){
                        var prev = $btn.text();
                        $btn.text('Copied');
                        setTimeout(function(){ $btn.text(prev); }, 1400);
                    }).catch(function(){ showToast('danger', 'Unable to copy'); });
                } else {
                    // fallback
                    var $temp = $('<input>');
                    $('body').append($temp);
                    $temp.val(stock).select();
                    try { document.execCommand('copy'); $btn.text('Copied'); setTimeout(function(){ $btn.text('Copy ID'); $temp.remove(); }, 1400); } catch(e){ showToast('danger', 'Copy failed'); $temp.remove(); }
                }
            });
        });
        </script>
    </body>
</html>