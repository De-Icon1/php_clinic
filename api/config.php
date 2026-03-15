<?php
// Simple API DB config for cross-system lookups.
// Adjust these values to match your actual MySQL setup.

$API_DB_HOST        = 'localhost';
$API_DB_USER        = 'root';
$API_DB_PASS        = '';

// Name of the student portal database (where biodata lives)
$API_STUDENT_DB     = 'STUDENT_PORTAL_DB_NAME_HERE';

// Name of the medical portal database that contains med_data (from med_data.sql)
$API_MEDICAL_DB     = 'oict_mission';

function api_get_connection($dbName)
{
    global $API_DB_HOST, $API_DB_USER, $API_DB_PASS;

    $mysqli = @new mysqli($API_DB_HOST, $API_DB_USER, $API_DB_PASS, $dbName);
    if ($mysqli->connect_error) {
        http_response_code(500);
        echo json_encode(array(
            'success' => false,
            'error'   => 'Database connection failed for ' . $dbName,
        ));
        exit;
    }

    // Ensure utf8 encoding
    $mysqli->set_charset('utf8');
    return $mysqli;
}

function api_send_json($payload, $statusCode = 200)
{
    if (!headers_sent()) {
        header('Content-Type: application/json');
        // Allow cross-origin callers; tighten this in production.
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    }
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

?>
