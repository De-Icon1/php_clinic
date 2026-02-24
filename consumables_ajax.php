<?php
session_start();
include('assets/inc/config.php'); // DB connection

/* ============================================================
   AJAX: FETCH CONSUMABLES BASED ON USER WORKING LOCATION
=============================================================== */
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');

    // Accept either a numeric `working_location_id` or a text `working_location` name
    if ((!isset($_SESSION['working_location']) || empty($_SESSION['working_location'])) && (!isset($_SESSION['working_location_id']) || empty($_SESSION['working_location_id']))) {
        echo json_encode(['status'=>'error','message'=>'Working location not set.']);
        exit;
    }

    $use_id = false;
    $location_id = null;
    if (isset($_SESSION['working_location_id']) && intval($_SESSION['working_location_id']) > 0) {
        $use_id = true;
        $location_id = intval($_SESSION['working_location_id']);
    } else {
        $location = $_SESSION['working_location'];
    }

    // Fetch consumables filtered by campus_id when we have an id, otherwise fall back to name
    if ($use_id) {
        $query = "SELECT 
                    s.id,
                    c.name AS consumable_name,
                    c.category,
                    s.quantity,
                    cl.name AS location
                  FROM lab_consumable_stock s
                  JOIN lab_consumables c ON c.id = s.consumable_id
                  JOIN campus_locations cl ON cl.id = s.campus_id
                  WHERE s.campus_id = ?
                  ORDER BY c.name ASC";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $location_id);
        $stmt->execute();
    } else {
        $query = "SELECT 
                    s.id,
                    c.name AS consumable_name,
                    c.category,
                    s.quantity,
                    cl.name AS location
                  FROM lab_consumable_stock s
                  JOIN lab_consumables c ON c.id = s.consumable_id
                  JOIN campus_locations cl ON cl.id = s.campus_id
                  WHERE s.campus_id = (
                        SELECT id FROM campus_locations WHERE name = ?
                  )
                  ORDER BY c.name ASC";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $location);
        $stmt->execute();
    }
    $result = $stmt->get_result();

    $consumables = [];
    while ($row = $result->fetch_assoc()) {
        $consumables[] = $row;
    }

    echo json_encode(['status'=>'success','data'=>$consumables]);
    exit;
}

/* ============================================================
   AJAX: PICK CONSUMABLE — DEDUCT FROM BOTH TABLES
=============================================================== */
if (isset($_POST['pick']) && isset($_POST['id']) && isset($_POST['qty'])) {
    header('Content-Type: application/json');

    // Require a working location so we always pick from a specific campus store
    if ((!isset($_SESSION['working_location']) || empty($_SESSION['working_location'])) &&
        (!isset($_SESSION['working_location_id']) || empty($_SESSION['working_location_id']))) {
        echo json_encode(['status'=>'error','message'=>'Working location not set.']);
        exit;
    }

    $id  = intval($_POST['id']);
    $qty = intval($_POST['qty']);

    if ($qty <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid quantity.']);
        exit;
    }

    // Resolve campus/location id (prefer stored id, fall back to name lookup)
    $campus_id = null;
    if (isset($_SESSION['working_location_id']) && intval($_SESSION['working_location_id']) > 0) {
        $campus_id = intval($_SESSION['working_location_id']);
    } else {
        $locationName = $_SESSION['working_location'];
        $locStmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
        if ($locStmt) {
            $locStmt->bind_param('s', $locationName);
            $locStmt->execute();
            $locRes = $locStmt->get_result();
            if ($locRow = $locRes->fetch_assoc()) {
                $campus_id = intval($locRow['id']);
            }
        }
    }

    if (!$campus_id) {
        echo json_encode(['status'=>'error','message'=>'Unable to resolve working location.']);
        exit;
    }

    // Start safe transaction so deductions are atomic per campus store
    $mysqli->begin_transaction();

    try {
        /* ------------------------------------------------------
           1. CHECK STOCK TABLE FOR THIS ID AND CAMPUS
        ------------------------------------------------------ */
        $stmt = $mysqli->prepare("
            SELECT quantity 
            FROM lab_consumable_stock 
            WHERE id = ? AND campus_id = ?
        ");
        $stmt->bind_param("ii", $id, $campus_id);
        $stmt->execute();
        $stockResult = $stmt->get_result();

        if ($stockResult->num_rows === 0) {
            throw new Exception("Consumable not found for your current location.");
        }

        $stockRow = $stockResult->fetch_assoc();
        $stockQty = $stockRow['quantity'];

        if ($qty > $stockQty) {
            throw new Exception("Quantity exceeds available stock at this location.");
        }

        /* ------------------------------------------------------
           2. UPDATE lab_consumable_stock ONLY FOR THIS CAMPUS
        ------------------------------------------------------ */
        $newStockQty = $stockQty - $qty;

        $stmt = $mysqli->prepare("
            UPDATE lab_consumable_stock 
            SET quantity = ? 
            WHERE id = ? AND campus_id = ?
        ");
        $stmt->bind_param("iii", $newStockQty, $id, $campus_id);
        $stmt->execute();

        /* ------------------------------------------------------
           3. COMMIT TRANSACTION
        ------------------------------------------------------ */
        $mysqli->commit();

        echo json_encode(['status' => 'success', 'message' => 'Consumable picked successfully.']);
    }
    catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }

    exit;
}

/* ============================================================
   SET OR CHANGE WORKING LOCATION
=============================================================== */
if (isset($_POST['set_location'])) {
    $wl = $_POST['working_location'];
    if (is_numeric($wl)) {
        $id = intval($wl);
        $stmt = $mysqli->prepare("SELECT name FROM campus_locations WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $r = $stmt->get_result();
            if ($row = $r->fetch_assoc()) {
                $_SESSION['working_location_id'] = $id;
                $_SESSION['working_location'] = $row['name'];
            } else {
                $_SESSION['working_location_id'] = $id;
                $_SESSION['working_location'] = '';
            }
        }
    } else {
        $_SESSION['working_location'] = $wl;
        $stmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $wl);
            $stmt->execute();
            $r = $stmt->get_result();
            if ($row = $r->fetch_assoc()) {
                $_SESSION['working_location_id'] = intval($row['id']);
            }
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['clear_location'])) {
    unset($_SESSION['working_location']);
    unset($_SESSION['working_location_id']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

    <!-- Head -->
    <?php include('assets/inc/head.php'); ?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar -->
            <?php include('assets/inc/nav_r.php'); ?>

            <!-- Left Sidebar: use Lab Navigation instead of Admin -->
            <?php include('assets/inc/slidebar_lab.php'); ?>

            <!-- Start Page Content here -->
            <div class="content-page">
                <div class="content">
                    <div class="container-fluid">

                        <h3 class="mb-4">Lab Consumables (Pick Items)</h3>

                        <?php if (!isset($_SESSION['working_location']) && !isset($_SESSION['working_location_id'])): ?>
        <form method="post" class="mb-4">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <select name="working_location" class="form-control" required>
                        <option value="">-- Select Your Working Location --</option>
                        <?php
                        $campuses = $mysqli->query("SELECT id, name FROM campus_locations ORDER BY name ASC");
                        while($c = $campuses->fetch_assoc()){
                            echo "<option value='".intval($c['id'])."'>".htmlspecialchars($c['name'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" name="set_location" class="btn btn-primary w-100">Set Location</button>
                </div>
            </div>
        </form>

    <?php else: ?>
        <p>
            <strong>Current Working Location:</strong> <?= $_SESSION['working_location'] ?>
            <a href="?clear_location=1" class="btn btn-sm btn-warning ms-2">Change Location</a>
        </p>

        <div class="table-responsive">
            <table class="table table-bordered" id="consumable_table">
                <thead class="thead-light">
                    <tr>
                        <th>Consumable</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Location</th>
                        <th>Pick Quantity</th>
                    </tr>
                </thead>
                <tbody id="consumable_results">
                    <tr><td colspan="5" class="text-center text-muted">Loading consumables...</td></tr>
                </tbody>
            </table>
        </div>

        <script>
        async function fetchConsumables() {
            const res = await fetch('<?= $_SERVER['PHP_SELF'] ?>?ajax=1');
            const data = await res.json();
            const tbody = document.getElementById('consumable_results');
            tbody.innerHTML = '';

            if (data.status === 'success') {
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No consumables found</td></tr>';
                    return;
                }

                data.data.forEach(row => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${row.consumable_name}</td>
                            <td>${row.category}</td>
                            <td>${row.quantity}</td>
                            <td>${row.location}</td>
                            <td>
                                <input type="number" min="1" max="${row.quantity}" value="1" 
                                       class="form-control form-control-sm d-inline-block" 
                                       style="width:80px" id="pick_qty_${row.id}">
                                <button class="btn btn-sm btn-success ms-1" onclick="pickConsumable(${row.id}, ${row.quantity})">Pick</button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">'+data.message+'</td></tr>';
            }
        }

        async function pickConsumable(id, maxQty) {
            const input = document.getElementById('pick_qty_' + id);
            let qty = parseInt(input.value, 10);

            if (isNaN(qty) || qty <= 0) {
                alert('Please enter a valid quantity.');
                return;
            }

            if (qty > maxQty) {
                alert('You cannot pick more than the available quantity (' + maxQty + ').');
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('qty', qty);
            formData.append('pick', 1);

            const res = await fetch('<?= $_SERVER['PHP_SELF'] ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            alert(data.message);
            fetchConsumables();
        }

        document.addEventListener('DOMContentLoaded', fetchConsumables);
        </script>
    <?php endif; ?>

                    </div> <!-- container-fluid -->
                </div> <!-- content -->
            </div> <!-- content-page -->

        </div> <!-- wrapper -->

    </body>

</html>