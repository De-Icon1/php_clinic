<?php
session_start();
include('assets/inc/config.php');

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

    $campus = $mysqli->prepare("SELECT id FROM campus_locations WHERE name=?");
    $campus->bind_param("s", $campus_name);
    $campus->execute();
    $res = $campus->get_result()->fetch_assoc();
    $campus_id = $res['id'] ?? null;

    if(!$campus_id){
        $err = "Selected campus does not exist";
    } else {
        $check = $mysqli->prepare("SELECT id FROM lab_consumable_stock WHERE consumable_id=? AND campus_id=?");
        $check->bind_param("ii", $consumable_id, $campus_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();

        if($row){
            $update = $mysqli->prepare("UPDATE lab_consumable_stock SET quantity = quantity + ? WHERE id=?");
            $update->bind_param("ii", $quantity, $row['id']);
            $update->execute();
        } else {
            $insert = $mysqli->prepare("INSERT INTO lab_consumable_stock (consumable_id, campus_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $consumable_id, $campus_id, $quantity);
            $insert->execute();
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
        $check = $mysqli->prepare("SELECT quantity, id FROM lab_consumable_stock WHERE consumable_id=? AND campus_id=?");
        $check->bind_param("ii", $consumable_id, $from_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();

        if(!$row || $row['quantity'] < $quantity){
            $err = "Not enough stock in source campus.";
        } else {
            $update_from = $mysqli->prepare("UPDATE lab_consumable_stock SET quantity = quantity - ? WHERE id=?");
            $update_from->bind_param("ii", $quantity, $row['id']);
            $update_from->execute();

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
    fputcsv($output, ['Consumable Name','Category','Campus','Quantity']);

    $sql = "SELECT s.quantity, cl.name AS campus_name, c.name AS consumable_name, c.category
            FROM lab_consumable_stock s
            JOIN lab_consumables c ON c.id = s.consumable_id
            JOIN campus_locations cl ON cl.id = s.campus_id
            ORDER BY cl.name ASC, c.name ASC";
    $res = $mysqli->query($sql);
    while($row = $res->fetch_assoc()){
        fputcsv($output, [$row['consumable_name'], $row['category'], $row['campus_name'], $row['quantity']]);
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
            if($row++ == 0) continue;
            list($name, $category, $campus_name, $quantity) = $data;

            $stmt = $mysqli->prepare("SELECT id FROM lab_consumables WHERE name=? AND category=?");
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

            $check = $mysqli->prepare("SELECT id FROM lab_consumable_stock WHERE consumable_id=? AND campus_id=?");
            $check->bind_param("ii", $consumable_id, $campus_id);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();

            if($existing){
                $update = $mysqli->prepare("UPDATE lab_consumable_stock SET quantity=? WHERE id=?");
                $update->bind_param("ii", $quantity, $existing['id']);
                $update->execute();
            } else {
                $insert = $mysqli->prepare("INSERT INTO lab_consumable_stock (consumable_id, campus_id, quantity) VALUES (?,?,?)");
                $insert->bind_param("iii", $consumable_id, $campus_id, $quantity);
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

                <!-- Add Campus Location -->
                <div class="card">
                    <div class="card-body">
                        <h4>Add Campus Location</h4>
                        <form method="post">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Campus Name</label>
                                    <input type="text" required name="campus_name" class="form-control" placeholder="e.g. Mini Campus Health Centre">
                                </div>
                            </div>
                            <button type="submit" name="add_campus" class="btn btn-primary">Add Campus</button>
                        </form>
                    </div>
                </div>

                <hr>

                <!-- List Campus Locations -->
                <div class="card">
                    <div class="card-body">
                        <h4>Registered Campuses</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Campus Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $campus_list = $mysqli->query("SELECT * FROM campus_locations ORDER BY name ASC");
                                while($row = $campus_list->fetch_assoc()){
                                ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td><?= $row['name']; ?></td>
                                    <td>
                                        <a href="setup_consumables.php?del_campus=<?= $row['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this campus?');">
                                            <img src="assets/images/del.png" height="20">
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

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

                <!-- rest of your Add Stock, Transfer Stock, CSV import/export, consumables table, stock table remain unchanged -->

            </div>
        </div>
        <?php include("assets/inc/footer.php"); ?>
    </div>
</div>

<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>
</body>
</html>