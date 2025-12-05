<?php
session_start();
include('assets/inc/config.php');

/* =============================
   LOAD DRUG INFO USING ONLY ID
============================= */

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No drug selected.");
}

$id = intval($_GET['id']);

// Fetch drug info safely with aliased quantity
$stmt = $mysqli->prepare("SELECT id, name, quantity AS qnt, category FROM drug WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("Invalid drug selected.");
}

$drug = $res->fetch_assoc();

// Assign variables for your form
$name = $drug['name'];
$qnt  = $drug['qnt'];
$cate = $drug['category'];

/* =============================
   HANDLE DRUG MOVEMENT
============================= */

if (isset($_POST['move'])) {

    $move_qnt     = $_POST['mqnt'] ?? 0;
    $location_id  = $_POST['location_id'] ?? '';

    // VALIDATION
    if ($location_id == '') {
        $error = "Please select a pharmacy location.";
    } elseif (!is_numeric($move_qnt) || $move_qnt <= 0) {
        $error = "Quantity must be a valid number greater than zero.";
    } elseif ($move_qnt > $qnt) {
        $error = "Only $qnt available in store, cannot move $move_qnt.";
    } else {

        // Begin transaction
        $mysqli->begin_transaction();

        try {
            // Deduct from main store
            $new_store_qnt = $qnt - $move_qnt;
            $updateStore = $mysqli->prepare("UPDATE drug SET quantity=? WHERE id=?");
            $updateStore->bind_param("ii", $new_store_qnt, $id);
            $updateStore->execute();

            // Check if drug already exists for this location
            $checkLocationStock = $mysqli->prepare("SELECT quantity FROM pharmacy WHERE name=? AND pharmacy_location_id=?");
            $checkLocationStock->bind_param("si", $name, $location_id);
            $checkLocationStock->execute();
            $resLoc = $checkLocationStock->get_result();

            if ($resLoc->num_rows > 0) {
                // Update existing row for this location
                $rowLoc = $resLoc->fetch_assoc();
                $newQty = $rowLoc['quantity'] + $move_qnt;

                $update = $mysqli->prepare("UPDATE pharmacy SET quantity=? WHERE name=? AND pharmacy_location_id=?");
                $update->bind_param("isi", $newQty, $name, $location_id);
                $update->execute();
            } else {
                // Insert new row for this location
                $insert = $mysqli->prepare("INSERT INTO pharmacy (name, quantity, amount, category, pharmacy_location_id) VALUES (?, ?, 0, ?, ?)");
                $insert->bind_param("sisi", $name, $move_qnt, $cate, $location_id);
                $insert->execute();
            }

            $mysqli->commit();
            $success = "Drug successfully moved to selected pharmacy location.";
            $qnt = $new_store_qnt; // update displayed store quantity

        } catch (Exception $e) {
            $mysqli->rollback();
            $error = "Error processing movement: " . $e->getMessage();
        }
    }
}

/* =============================
   LOAD PHARMACY LOCATIONS
============================= */
$loc_q = $mysqli->query("SELECT * FROM pharmacy_location ORDER BY name ASC");
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
                            <h4 class="page-title">Stock Movement in Drugs Quantity</h4>
                        </div>
                    </div>
                </div>

                <!-- Success / Error Messages -->
                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php } ?>

                <?php if (isset($success)) { ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php } ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <form method="post">

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label><strong>Drug Name</strong></label>
                                            <input type="text" class="form-control" value="<?php echo $name; ?>" disabled>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label><strong>Store Quantity</strong></label>
                                            <input type="text" class="form-control" value="<?php echo $qnt; ?>" disabled>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label><strong>Category</strong></label>
                                            <input type="text" class="form-control" value="<?php echo $cate; ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="form-row">

                                        <div class="form-group col-md-3">
                                            <label><strong>Select Location</strong></label>
                                            <select name="location_id" class="form-control" required>
                                                <option value="">-- Select Location --</option>
                                                <?php while ($loc = $loc_q->fetch_assoc()) { ?>
                                                    <option value="<?php echo $loc['id']; ?>">
                                                        <?php echo $loc['name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label><strong>Enter Quantity</strong></label>
                                            <input type="number" name="mqnt" class="form-control" placeholder="Enter quantity" required>
                                        </div>

                                    </div>

                                    <button type="submit" name="move" class="btn btn-primary">
                                        Move Drug to Pharmacy
                                    </button>

                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <?php include('assets/inc/footer.php'); ?>

    </div>

</div>

</body>
</html>