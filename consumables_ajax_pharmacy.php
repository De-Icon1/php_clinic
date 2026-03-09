<?php
session_start();
include('assets/inc/config.php'); // DB connection

/* ============================================================
   AJAX: FETCH PHARMACY CONSUMABLES BASED ON USER WORKING LOCATION
=============================================================== */
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');

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

    if ($use_id) {
        $query = "SELECT 
                    s.id,
                    c.name AS consumable_name,
                    c.category,
                    s.quantity,
                    cl.name AS location
                  FROM pharmacy_consumable_stock s
                  JOIN pharmacy_consumables c ON c.id = s.consumable_id
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
                  FROM pharmacy_consumable_stock s
                  JOIN pharmacy_consumables c ON c.id = s.consumable_id
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
   AJAX: PICK CONSUMABLE — DEDUCT FROM PHARMACY STOCK
=============================================================== */
if (isset($_POST['pick']) && isset($_POST['id']) && isset($_POST['qty'])) {
    header('Content-Type: application/json');

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

    $mysqli->begin_transaction();

    try {
        $stmt = $mysqli->prepare("SELECT quantity FROM pharmacy_consumable_stock WHERE id = ? AND campus_id = ?");
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

        $newStockQty = $stockQty - $qty;

        $stmt = $mysqli->prepare("UPDATE pharmacy_consumable_stock SET quantity = ? WHERE id = ? AND campus_id = ?");
        $stmt->bind_param("iii", $newStockQty, $id, $campus_id);
        $stmt->execute();

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
