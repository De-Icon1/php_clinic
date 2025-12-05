<?php
session_start();
include('assets/inc/config.php'); // DB connection

$success = '';
$err = '';

/* ============================================================
   AJAX: FETCH CONSUMABLES BASED ON USER WORKING LOCATION
=============================================================== */
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['working_location']) || empty($_SESSION['working_location'])) {
        echo json_encode(['status'=>'error','message'=>'Working location not set.']);
        exit;
    }

    $location = $_SESSION['working_location'];

    // Strictly fetch consumables from current campus/location
    $query = "SELECT 
                s.id,
                c.name AS consumable_name,
                c.category,
                s.quantity,
                cl.name AS location,
                s.date_added
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
    $result = $stmt->get_result();

    $consumables = [];
    while ($row = $result->fetch_assoc()) {
        $consumables[] = $row;
    }

    echo json_encode(['status'=>'success','data'=>$consumables]);
    // DEBUGGING
file_put_contents("debug.txt", print_r($consumables, true));
    exit;
}

/* ============================================================
   AJAX: PICK CONSUMABLE — DEDUCT FROM BOTH TABLES
=============================================================== */
if (isset($_POST['pick']) && isset($_POST['id']) && isset($_POST['qty'])) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['working_location'])) {
        echo json_encode(['status'=>'error','message'=>'Working location not set.']);
        exit;
    }

    $id  = intval($_POST['id']);
    $qty = intval($_POST['qty']);

    if ($qty <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid quantity.']);
        exit;
    }

    // Start safe transaction
    $mysqli->begin_transaction();

    try {
        /* ------------------------------------------------------
           1. CHECK STOCK TABLE FOR THIS ID
        ------------------------------------------------------ */
        $stmt = $mysqli->prepare("
            SELECT quantity, consumable_id 
            FROM lab_consumable_stock 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stockResult = $stmt->get_result();

        if ($stockResult->num_rows === 0) {
            throw new Exception("Consumable not found in stock table.");
        }

        $stockRow = $stockResult->fetch_assoc();
        $stockQty = $stockRow['quantity'];
        $consumableId = $stockRow['consumable_id'];

        if ($qty > $stockQty) {
            throw new Exception("Quantity exceeds available location stock.");
        }

        /* ------------------------------------------------------
           2. CHECK MAIN lab_consumables TABLE
        ------------------------------------------------------ */
        $stmt = $mysqli->prepare("
            SELECT quantity 
            FROM lab_consumables 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $consumableId);
        $stmt->execute();
        $consResult = $stmt->get_result();

        if ($consResult->num_rows === 0) {
            throw new Exception("Consumable missing from main table.");
        }

        $consRow = $consResult->fetch_assoc();
        $mainQty = $consRow['quantity'];

        if ($qty > $mainQty) {
            throw new Exception("Master stock insufficient.");
        }

        /* ------------------------------------------------------
           3. UPDATE lab_consumable_stock
        ------------------------------------------------------ */
        $newStockQty = $stockQty - $qty;

        $stmt = $mysqli->prepare("
            UPDATE lab_consumable_stock 
            SET quantity = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $newStockQty, $id);
        $stmt->execute();

        /* ------------------------------------------------------
           4. UPDATE lab_consumables (MASTER TABLE)
        ------------------------------------------------------ */
        $newMainQty = $mainQty - $qty;

        $stmt = $mysqli->prepare("
            UPDATE lab_consumables 
            SET quantity = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $newMainQty, $consumableId);
        $stmt->execute();

        /* ------------------------------------------------------
           5. COMMIT TRANSACTION
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
    $_SESSION['working_location'] = $_POST['working_location'];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['clear_location'])) {
    unset($_SESSION['working_location']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Consumables - Pick Items</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container-fluid">
    <h3 class="mb-4">Lab Consumables (Pick Items)</h3>

    <?php if (!isset($_SESSION['working_location'])): ?>
        <form method="post" class="mb-4">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <select name="working_location" class="form-control" required>
                        <option value="">-- Select Your Working Location --</option>
                        <?php
                        $campuses = $mysqli->query("SELECT name FROM campus_locations ORDER BY name ASC");
                        while($c = $campuses->fetch_assoc()){
                            echo "<option value='{$c['name']}'>{$c['name']}</option>";
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="consumable_results">
                    <tr><td colspan="5" class="text-center text-muted">Loading consumables...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="pickModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pick Consumable</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="pickForm">
                            <input type="hidden" name="id" id="modal_id">
                            <div class="mb-3">
                                <label for="modal_qty" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="modal_qty" name="qty" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Pick</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="assets/js/bootstrap.bundle.min.js"></script>

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
                            <td><button class="btn btn-sm btn-success" onclick="openPickModal(${row.id}, ${row.quantity})">Pick</button></td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">'+data.message+'</td></tr>';
            }
        }

        function openPickModal(id, maxQty) {
            document.getElementById('modal_id').value = id;
            const qtyInput = document.getElementById('modal_qty');
            qtyInput.value = '';
            qtyInput.max = maxQty;
            const modal = new bootstrap.Modal(document.getElementById('pickModal'));
            modal.show();
        }

        document.getElementById('pickForm').addEventListener('submit', async function(e){
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('pick', 1);

            const res = await fetch('<?= $_SERVER['PHP_SELF'] ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('pickModal')).hide();
            fetchConsumables();
        });

        document.addEventListener('DOMContentLoaded', fetchConsumables);
        </script>
    <?php endif; ?>
</div>
</body>
</html>