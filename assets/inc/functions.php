<?php
function log_action($user_id, $action) {
    global $mysqli; // ✅ so it can use $mysqli from config.php

    $ipaddress = $_SERVER['REMOTE_ADDR']; // correct IP
    $log_stmt = $mysqli->prepare("INSERT INTO logs (user_id, action, mac) VALUES (?, ?, ?)");
    if (!$log_stmt) {
        return false;
    }
    $log_stmt->bind_param('iss', $user_id, $action, $ipaddress);
    try {
        $log_stmt->execute();
    } catch (mysqli_sql_exception $e) {
        // If logs table isn't configured correctly (e.g. id not AUTO_INCREMENT),
        // fail gracefully without breaking the login flow. Record to PHP error log.
        error_log('log_action() failed: ' . $e->getMessage());
        $log_stmt->close();
        return false;
    }
    $log_stmt->close();
    return true;
}
?>