<?php
// Endpoint to set campus in session (AJAX)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

$resp = ['success' => false, 'msg' => 'Unknown error'];
if (!isset($_POST['campus_id'])) {
    $resp['msg'] = 'Missing campus_id';
    echo json_encode($resp);
    exit;
}

$cid = (int) $_POST['campus_id'];
// 0 => clear selection (all campuses)
if ($cid === 0) {
    unset($_SESSION['campus_id']);
    $resp = ['success' => true, 'msg' => 'Campus cleared'];
    echo json_encode($resp);
    exit;
}

// Helper: check if a table exists
function table_exists($mysqli, $table)
{
    $t = $mysqli->real_escape_string($table);
    $q = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $t . "'";
    $r = $mysqli->query($q);
    if ($r) {
        return (int) $r->fetch_assoc()['cnt'] > 0;
    }
    return false;
}

// Try to validate campus id against a known campus table
$valid = false;
$campusTables = ['campus_locations', 'campuses', 'locations', 'his_campus'];
foreach ($campusTables as $tbl) {
    if (table_exists($mysqli, $tbl)) {
        $s = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM {$tbl} WHERE id = ? LIMIT 1");
        if ($s) {
            $s->bind_param('i', $cid);
            $s->execute();
            $res = $s->get_result()->fetch_assoc();
            if ($res && (int) $res['cnt'] > 0) {
                $valid = true;
            }
            $s->close();
        }
        break;
    }
}

// If no campus table, check distinct campus ids in pharmacy/store_stock/pharmacy_stock
if (!$valid) {
    $tablesToCheck = ['pharmacy', 'store_stock', 'pharmacy_stock'];
    foreach ($tablesToCheck as $t) {
        if (table_exists($mysqli, $t)) {
            $q = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM {$t} WHERE campus_id = ? LIMIT 1");
            if ($q) {
                $q->bind_param('i', $cid);
                $q->execute();
                $res = $q->get_result()->fetch_assoc();
                if ($res && (int) $res['cnt'] > 0) {
                    $valid = true;
                    $q->close();
                    break;
                }
                $q->close();
            }
        }
    }
}

if ($valid) {
    $_SESSION['campus_id'] = $cid;
    $resp = ['success' => true, 'msg' => 'Campus set'];
} else {
    $resp = ['success' => false, 'msg' => 'Invalid campus id'];
}

echo json_encode($resp);
exit;

?>
