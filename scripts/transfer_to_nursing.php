<?php
session_start();
include(__DIR__ . '/..//assets/inc/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['err'] = 'Invalid request method.';
    header('Location: ../transfer_drug_nursing.php');
    exit();
}

// Accept target campus/location for the transfer
$drug_id = isset($_POST['drug_id']) ? intval($_POST['drug_id']) : 0;
$qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$campus_id = isset($_POST['campus_id']) ? intval($_POST['campus_id']) : 0;
$new_location = isset($_POST['new_location']) ? trim($_POST['new_location']) : '';
$patient_code = isset($_POST['patient_code']) ? $mysqli->real_escape_string(trim($_POST['patient_code'])) : '';
$notes = isset($_POST['notes']) ? $mysqli->real_escape_string(trim($_POST['notes'])) : '';

if ($drug_id <= 0 || $qty <= 0) {
    $_SESSION['err'] = 'Please select a drug and enter a valid quantity.';
    header('Location: ../transfer_drug_nursing.php');
    exit();
}

// If a new_location is provided, create it and use it
if (!empty($new_location)) {
    $nl = $mysqli->real_escape_string($new_location);
    // check exists
    $chk = $mysqli->prepare("SELECT id FROM campus_locations WHERE name=? LIMIT 1");
    $chk->bind_param('s', $nl);
    $chk->execute();
    $cres = $chk->get_result();
    if ($cres && $cres->num_rows) {
        $campus_id = $cres->fetch_assoc()['id'];
    } else {
        $mysqli->query("INSERT INTO campus_locations (name) VALUES ('".$nl."')");
        $campus_id = $mysqli->insert_id;
        if ($campus_id == 0) {
            $tmp = $mysqli->query("SELECT id FROM campus_locations WHERE name='".$nl."'")->fetch_assoc();
            $campus_id = $tmp['id'] ?? 0;
        }
    }
}

if ($campus_id <= 0) {
    $_SESSION['err'] = 'Please select or create a transfer location.';
    header('Location: ../transfer_drug_nursing.php');
    exit();
}

// Fetch current drug
$stmt = $mysqli->prepare("SELECT name, quantity, supplier_name, lpo_ref, category FROM drug WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $drug_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $_SESSION['err'] = 'Drug not found.';
    header('Location: ../transfer_drug_nursing.php');
    exit();
}
$drug = $res->fetch_assoc();
$current_qty = intval($drug['quantity']);

if ($qty > $current_qty) {
    $_SESSION['err'] = 'Insufficient drug stock. Current: '.$current_qty;
    header('Location: ../transfer_drug_nursing.php');
    exit();
}

// Begin transaction
$mysqli->begin_transaction();
try {
    // Deduct from drug table
    $upd = $mysqli->prepare("UPDATE drug SET quantity = quantity - ? WHERE id = ?");
    $upd->bind_param('ii', $qty, $drug_id);
    $upd->execute();
    if ($upd->affected_rows === 0) throw new Exception('Failed to update drug quantity.');

    // Map drug to a nurse consumable (create if missing)
    $name = $drug['name'];
    $category = !empty($drug['category']) ? $drug['category'] : 'Drug';
    $supplier = !empty($drug['supplier_name']) ? $drug['supplier_name'] : '';
    $lpo = !empty($drug['lpo_ref']) ? $drug['lpo_ref'] : '';

    // find or create nurse_consumables entry
    $nc_stmt = $mysqli->prepare("SELECT id FROM nurse_consumables WHERE name=? LIMIT 1");
    if (!$nc_stmt) throw new Exception('DB prepare error (nurse_consumables select)');
    $nc_stmt->bind_param('s', $name);
    $nc_stmt->execute();
    $nc_res = $nc_stmt->get_result();
    if ($nc_res && $nc_res->num_rows) {
        $consumable_id = $nc_res->fetch_assoc()['id'];
    } else {
        $ins_nc = $mysqli->prepare("INSERT INTO nurse_consumables (name, category) VALUES (?, ?)");
        if (!$ins_nc) throw new Exception('DB prepare error (nurse_consumables insert)');
        $ins_nc->bind_param('ss', $name, $category);
        $ins_nc->execute();
        if ($ins_nc->affected_rows === 0) throw new Exception('Failed to create nurse consumable.');
        $consumable_id = $ins_nc->insert_id;
    }

    // validate provided campus_id exists
    $cstmt = $mysqli->prepare("SELECT id, name FROM campus_locations WHERE id=? LIMIT 1");
    $cstmt->bind_param('i', $campus_id);
    $cstmt->execute();
    $cres = $cstmt->get_result();
    if ($cres && $cres->num_rows) {
        $campus_row = $cres->fetch_assoc();
    } else {
        throw new Exception('Selected transfer location not found.');
    }

    // add or update stock record in nurse_consumable_stock (for selected campus)
    $check = $mysqli->prepare("SELECT id, quantity FROM nurse_consumable_stock WHERE consumable_id=? AND campus_id=? LIMIT 1");
    $check->bind_param('ii', $consumable_id, $campus_id);
    $check->execute();
    $chkres = $check->get_result();
    if ($chkres && $chkres->num_rows) {
        $row = $chkres->fetch_assoc();
        $stock_id = $row['id'];
        $new_qty = intval($row['quantity']) + $qty;
        $upd2 = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity=?, supplier_name=?, lpo_ref=? WHERE id=?");
        $upd2->bind_param('issi', $new_qty, $supplier, $lpo, $stock_id);
        $upd2->execute();
        if ($upd2->affected_rows === 0) throw new Exception('Failed to update nurse stock.');
    } else {
        $ins2 = $mysqli->prepare("INSERT INTO nurse_consumable_stock (consumable_id, campus_id, quantity, supplier_name, lpo_ref) VALUES (?,?,?,?,?)");
        $ins2->bind_param('iiiss', $consumable_id, $campus_id, $qty, $supplier, $lpo);
        $ins2->execute();
        if ($ins2->affected_rows === 0) throw new Exception('Failed to insert nurse stock.');
    }

    $mysqli->commit();
    $_SESSION['success'] = 'Transferred '.$qty.' units of '.$name.' to Nursing (Sickbay).';
    header('Location: ../transfer_drug_nursing.php');
    exit();

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['err'] = 'Transfer failed: '.$e->getMessage();
    header('Location: ../transfer_drug_nursing.php');
    exit();
}
