<?php
session_start();
include('assets/inc/config.php');

// ================================
// DELETE LAB TEST
// ================================
if(isset($_GET['del'])){
    $id = $_GET['del'];
    $stmt = $mysqli->prepare("DELETE FROM Lab WHERE id=?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $success = $stmt ? "Laboratory Test Deleted Successfully" : "Please Try Again Later";
}

// ================================
// DELETE LAB CONSUMABLE ENTRY
// ================================
if(isset($_GET['del_stock'])){
    $id = $_GET['del_stock'];
    $stmt = $mysqli->prepare("DELETE FROM lab_location_stock WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $success = $stmt ? "Lab Consumable Deleted Successfully" : "Please Try Again Later";
}

// ================================
// REGISTER LAB TEST
// ================================
if(isset($_POST['lab'])){
    $name = $_POST['name'];
    $amount = $_POST['amount'];

    $stmt = $mysqli->prepare("INSERT INTO Lab(name,amount) VALUES(?,?)");
    $stmt->bind_param('ss',$name,$amount);
    $stmt->execute();
    $success = $stmt ? "Laboratory Registered Successfully" : "Please Try Again Later";
}

// ================================
// UPDATE LAB TEST
// ================================
if(isset($_POST['updatelab'])){
    $cname = $_POST['lname'];
    $camount = $_POST['lamount'];

    $stmt = $mysqli->prepare("UPDATE Lab SET amount=? WHERE name=?");
    $stmt->bind_param('ss',$camount,$cname);
    $stmt->execute();
    $success = $stmt ? "Laboratory Updated Successfully" : "Please Try Again Later";
}

// ================================
// ADD LAB CONSUMABLE
// ================================
if(isset($_POST['add_consumable'])){
    $lab_id = $_POST['lab_id'];
    $location_id = $_POST['location_id'];
    $quantity = $_POST['quantity'];

    $stmt = $mysqli->prepare("INSERT INTO lab_location_stock(lab_id, location_id, quantity) VALUES(?,?,?)");
    $stmt->bind_param("iii", $lab_id, $location_id, $quantity);
    $stmt->execute();
    $success = $stmt ? "Lab consumable added successfully" : "Failed to add consumable";
}

// ================================
// INLINE STOCK UPDATE
// ================================
if(isset($_POST['update_stock'])){
    $stock_id = $_POST['stock_id'];
    $quantity_change = $_POST['quantity_change'];
    $operation = $_POST['operation'];

    $stmt = $mysqli->prepare("SELECT quantity FROM lab_location_stock WHERE id=?");
    $stmt->bind_param("i", $stock_id);
    $stmt->execute();
    $stmt->bind_result($current_qty);
    $stmt->fetch();
    $stmt->close();

    $new_qty = ($operation === "add") ? $current_qty + $quantity_change : max(0, $current_qty - $quantity_change);

    $stmt = $mysqli->prepare("UPDATE lab_location_stock SET quantity=? WHERE id=?");
    $stmt->bind_param("ii", $new_qty, $stock_id);
    $stmt->execute();
    echo $stmt ? "success" : "error";
    exit;
}

// ================================
// CSV IMPORT
// ================================
if(isset($_POST['import_csv'])){
    if($_FILES['csv_file']['error'] == 0){
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        $row_count = 0;
        while(($line = fgetcsv($file)) !== FALSE){
            if($row_count == 0){ $row_count++; continue; } // skip header
            $lab_name = $line[0];
            $location_name = $line[1];
            $quantity = (int)$line[2];

            $lab_res = $mysqli->query("SELECT id FROM Lab WHERE name='".$mysqli->real_escape_string($lab_name)."'");
            $lab_row = $lab_res->fetch_assoc();
            if(!$lab_row) continue;
            $lab_id = $lab_row['id'];

            $loc_res = $mysqli->query("SELECT id FROM lab_locations WHERE name='".$mysqli->real_escape_string($location_name)."'");
            $loc_row = $loc_res->fetch_assoc();
            if(!$loc_row) continue;
            $location_id = $loc_row['id'];

            $check = $mysqli->query("SELECT id, quantity FROM lab_location_stock WHERE lab_id=$lab_id AND location_id=$location_id");
            if($check->num_rows > 0){
                $existing = $check->fetch_assoc();
                $new_qty = $existing['quantity'] + $quantity; // sum with existing
                $mysqli->query("UPDATE lab_location_stock SET quantity=$new_qty WHERE id=".$existing['id']);
            } else {
                $stmt = $mysqli->prepare("INSERT INTO lab_location_stock(lab_id, location_id, quantity) VALUES(?,?,?)");
                $stmt->bind_param("iii",$lab_id,$location_id,$quantity);
                $stmt->execute();
            }
        }
        fclose($file);
        $success = "CSV Imported and Synced Successfully!";
    } else {
        $err = "Error uploading CSV file!";
    }
}

// ================================
// CSV EXPORT
// ================================
if(isset($_POST['export_csv'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=lab_consumables.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Lab Name','Location','Quantity']);
    $res = $mysqli->query("SELECT l.name AS lab_name, ll.name AS location_name, s.quantity
                           FROM lab_location_stock s
                           JOIN Lab l ON s.lab_id = l.id
                           JOIN lab_locations ll ON s.location_id = ll.id
                           ORDER BY l.name ASC");
    while($row = $res->fetch_assoc()){
        fputcsv($output, [$row['lab_name'],$row['location_name'],$row['quantity']]);
    }
    fclose($output);
    exit;
}

// ================================
// FUNCTIONS
// ================================
function getlab($mysqli){
    $res = $mysqli->query("SELECT * FROM Lab ORDER BY name ASC");
    while($row = $res->fetch_assoc()){
        echo "<option value=\"".$row['name']."\">".$row['name']."</option>";
    }
}
function getLabLocations($mysqli){
    $res = $mysqli->query("SELECT * FROM lab_locations ORDER BY name ASC");
    while($row = $res->fetch_assoc()){
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
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
                <h4>Hospital Laboratory Management</h4>

                <!-- SweetAlert Notifications -->
                <?php if(isset($success)){ ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                Swal.fire({icon:'success',title:'Success',text:'<?php echo $success;?>',timer:3000,showConfirmButton:false});
                </script>
                <?php } ?>
                <?php if(isset($err)){ ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                Swal.fire({icon:'error',title:'Error',text:'<?php echo $err;?>',timer:3000,showConfirmButton:false});
                </script>
                <?php } ?>

                <!-- Register & Update Lab Forms -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-body">
                            <form method="post">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Lab Test Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Lab Test Amount</label>
                                        <input type="text" name="amount" class="form-control" required>
                                    </div>
                                </div>
                                <button type="submit" name="lab" class="btn btn-primary">Register Lab</button>
                            </form>
                            <hr>
                            <form method="post">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Select Lab Test</label>
                                        <select name="lname" class="form-control" required>
                                            <option>Choose</option>
                                            <?php getlab($mysqli); ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Updated Amount</label>
                                        <input type="text" name="lamount" class="form-control" required>
                                    </div>
                                </div>
                                <button type="submit" name="updatelab" class="btn btn-primary">Update Lab</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Add Consumable -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-body">
                            <form method="post">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Select Lab Test</label>
                                        <select name="lab_id" class="form-control" required>
                                            <option value="">Select Lab</option>
                                            <?php
                                            $labs = $mysqli->query("SELECT * FROM Lab ORDER BY name ASC");
                                            while($row = $labs->fetch_assoc()){
                                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Select Location</label>
                                        <select name="location_id" class="form-control" required>
                                            <option value="">Select Location</option>
                                            <?php getLabLocations($mysqli); ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Quantity</label>
                                        <input type="number" name="quantity" class="form-control" required>
                                    </div>
                                </div>
                                <button type="submit" name="add_consumable" class="btn btn-success">Add Consumable</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- CSV Import/Export -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-body">
                            <form method="post" enctype="multipart/form-data">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <input type="file" name="csv_file" class="form-control" accept=".csv">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <button type="submit" name="import_csv" class="btn btn-info">Import CSV</button>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <button type="submit" name="export_csv" class="btn btn-secondary">Export CSV</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Lab Tests Table -->
                <h4>Lab Tests</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lab Test Name</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $res = $mysqli->query("SELECT * FROM Lab ORDER BY id ASC");
                    while($row = $res->fetch_assoc()){
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['amount']}</td>
                                <td><a href='setup_laboratory.php?del={$row['id']}'><img src='assets/images/del.png' height='20'></a></td>
                              </tr>";
                    }
                    ?>
                    </tbody>
                </table>

                <!-- Lab Consumables Table -->
                <h4>Lab Consumables per Location</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Lab Test</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $res = $mysqli->query("SELECT s.id, l.name AS lab_name, ll.name AS location_name, s.quantity
                                           FROM lab_location_stock s
                                           JOIN Lab l ON s.lab_id = l.id
                                           JOIN lab_locations ll ON s.location_id = ll.id
                                           ORDER BY l.name ASC");
                    while($row = $res->fetch_assoc()){
                        echo "<tr>
                                <td>{$row['lab_name']}</td>
                                <td>{$row['location_name']}</td>
                                <td id='qty-{$row['id']}'>{$row['quantity']}</td>
                                <td>
                                    <button class='btn btn-success btn-sm' onclick='updateStock({$row['id']},\"add\")'>+ Add</button>
                                    <button class='btn btn-warning btn-sm' onclick='updateStock({$row['id']},\"subtract\")'>- Subtract</button>
                                    <a href='setup_laboratory.php?del_stock={$row['id']}'><img src='assets/images/del.png' height='20'></a>
                                </td>
                              </tr>";
                    }
                    ?>
                    </tbody>
                </table>

            </div>
        </div>
        <?php include('assets/inc/footer.php'); ?>
    </div>
</div>

<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function updateStock(stock_id, operation) {
    Swal.fire({
        title: 'Enter quantity',
        input: 'number',
        inputAttributes: { min: 1 },
        inputValue: 1,
        showCancelButton: true,
        confirmButtonText: 'Update',
    }).then((result) => {
        if(result.isConfirmed){
            const qty = result.value;
            fetch('setup_laboratory.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `update_stock=1&stock_id=${stock_id}&quantity_change=${qty}&operation=${operation}`
            })
            .then(response => response.text())
            .then(data => {
                const qtyCell = document.getElementById(`qty-${stock_id}`);
                let currentQty = parseInt(qtyCell.innerText);
                qtyCell.innerText = operation === 'add' ? currentQty + parseInt(qty) : Math.max(0, currentQty - parseInt(qty));
                Swal.fire({icon:'success',title:'Stock Updated',text:`Quantity successfully ${operation === 'add' ? 'added' : 'subtracted'}`,timer:2000,showConfirmButton:false});
            })
            .catch(err => {
                Swal.fire({icon:'error',title:'Error',text:'Failed to update stock!',timer:2000,showConfirmButton:false});
            });
        }
    });
}
</script>

</body>
</html>