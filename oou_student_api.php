<?php
// OOU UG Portal proxy API for HMS
// Protects with simple API key and normalises response structure.

header('Content-Type: application/json');

// --- Configuration ---
// External OOU API endpoint
define('OOU_API_URL', 'https://portal.oouagoiwoye.edu.ng/api/get_users.php');

// Credentials for the external OOU API
// TODO: replace with your real credentials in production
define('OOU_USERNAME', 'deicon');
define('OOU_PASSWORD', 'deicon');

// Shared secret between HMS and this proxy
// Change this to a strong, private value on your server
// Recommended: set environment variable HMS_STUDENT_API_KEY in production.
define('API_KEY', 'my_secure_hospital_key');

// Optional: limit which IPs can call this proxy.
// Add your web server / application server IPs here in production.
// Leave array empty to disable IP whitelisting.
define('ALLOWED_IPS', [
    '127.0.0.1',
    '::1',
]);

// Optionally pull in app config (DB, sessions, etc.)
// require_once __DIR__ . '/assets/inc/config.php';

// --- Helpers for security ---
function hms_get_client_ip()
{
    $keys = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR',
    ];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            // X_FORWARDED_FOR can contain a comma-separated list; take first
            $ipList = explode(',', $_SERVER[$key]);
            $ip = trim($ipList[0]);
            if ($ip !== '') {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

function hms_extract_api_key($authHeader)
{
    $authHeader = trim($authHeader);
    if ($authHeader === '') {
        return '';
    }

    // Support "Authorization: Bearer <key>" as well as raw key
    if (stripos($authHeader, 'Bearer ') === 0) {
        return trim(substr($authHeader, 7));
    }

    return $authHeader;
}

// --- Enforce IP whitelist (if configured) ---
$clientIp = hms_get_client_ip();
$allowedIps = is_array(ALLOWED_IPS) ? ALLOWED_IPS : [];
// Also trust the server's own IP address so local cURL
// calls from the same host are not blocked in production.
if (!empty($_SERVER['SERVER_ADDR'])) {
    $allowedIps[] = $_SERVER['SERVER_ADDR'];
}
if (!empty($allowedIps)) {
    if (!in_array($clientIp, $allowedIps, true)) {
        http_response_code(403);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Forbidden',
        ]);
        exit;
    }
}

// --- API key protection ---
// For internal calls originating from the same server (cURL from HMS PHP),
// we allow access without requiring the Authorization header. This avoids
// issues on hosts that strip custom Authorization headers, while still
// protecting against direct external access (blocked by IP whitelist).
$isInternal = !empty($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['SERVER_ADDR'])
    && $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'];

if (!$isInternal) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    $authHeader = '';
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) { // some servers normalise header names
        $authHeader = $headers['authorization'];
    }

    // Prefer environment variable if set; fall back to constant
    $expectedKey = getenv('HMS_STUDENT_API_KEY');
    if ($expectedKey === false || $expectedKey === '') {
        $expectedKey = API_KEY;
    }

    $providedKey = hms_extract_api_key($authHeader);

    if ($providedKey === '' || $providedKey !== $expectedKey) {
        http_response_code(401);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Unauthorized',
        ]);
        exit;
    }
}

// Only allow GET requests to this endpoint
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Method Not Allowed',
    ]);
    exit;
}

// --- Input parameters ---
// Supported student modes: UG, PG, CCED. If no specific type
// is provided (or "ALL" is used), the proxy will aggregate
// data across all three modes.
$allowedTypes = ['UG', 'PG', 'CCED'];
$rawType = isset($_GET['type']) ? strtoupper(trim($_GET['type'])) : 'ALL';
if ($rawType === '' || $rawType === 'ALL') {
    $type = 'ALL';
} elseif (in_array($rawType, $allowedTypes, true)) {
    $type = $rawType;
} else {
    $type = 'ALL';
}
$page  = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
$regnum = isset($_GET['regnum']) ? trim($_GET['regnum']) : '';

// Basic validation / normalisation
if ($page < 1) {
    $page = 1;
}
if ($limit < 1) {
    $limit = 20;
}
if ($limit > 100) {
    $limit = 100;
}

// --- Helper to fetch from external API for a single mode ---
function oou_fetch_mode($mode, $page, $limit, $regnum)
{
    // When a specific matric/regnum is requested, do not send page/limit
    // so the external API can return the full match without pagination.
    $queryParams = [
        'data'     => $mode,
        'username' => OOU_USERNAME,
        'password' => OOU_PASSWORD,
    ];

    if ($regnum !== '') {
        $queryParams['regnum'] = $regnum;
    } else {
        $queryParams['page']      = $page;
        $queryParams['page_size'] = $limit;
    }

    $query = http_build_query($queryParams);
    $url   = OOU_API_URL . '?' . $query;

    $context = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 20,
            'header'  => "User-Agent: HMS-OOU-Proxy\r\n",
        ],
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return ['error' => 'Failed to reach external OOU API'];
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return ['error' => 'Invalid response from external OOU API'];
    }

    return ['data' => $data];
}

// --- Fetch data from external API (one or all types) ---
$allRawStudents = [];

if ($type === 'ALL') {
    // Aggregate across all supported modes; when regnum is provided,
    // stop as soon as we find a match in any mode.
    foreach ($allowedTypes as $mode) {
        $result = oou_fetch_mode($mode, $page, $limit, $regnum);
        if (isset($result['error'])) {
            http_response_code(502);
            echo json_encode([
                'status'  => 'error',
                'message' => $result['error'],
            ]);
            exit;
        }
        $data = $result['data'];

        $rawForMode = [];
        if (isset($data['data']) && is_array($data['data'])) {
            $rawForMode = $data['data'];
        } elseif (array_keys($data) === range(0, count($data) - 1)) {
            $rawForMode = $data;
        } elseif (isset($data['regnum']) || isset($data['sname']) || isset($data['fname'])) {
            $rawForMode = [$data];
        }

        if ($regnum !== '') {
            if (!empty($rawForMode)) {
                $allRawStudents = $rawForMode;
                break;
            }
        } else {
            $allRawStudents = array_merge($allRawStudents, $rawForMode);
        }
    }
} else {
    $result = oou_fetch_mode($type, $page, $limit, $regnum);
    if (isset($result['error'])) {
        http_response_code(502);
        echo json_encode([
            'status'  => 'error',
            'message' => $result['error'],
        ]);
        exit;
    }
    $data = $result['data'];

    if (isset($data['data']) && is_array($data['data'])) {
        $allRawStudents = $data['data'];
    } elseif (array_keys($data) === range(0, count($data) - 1)) {
        $allRawStudents = $data;
    } elseif (isset($data['regnum']) || isset($data['sname']) || isset($data['fname'])) {
        $allRawStudents = [$data];
    }
}

// External API may either return:
//  - an array of students directly:      [ { ... }, { ... } ]
//  - a single student object:            { ... }
//  - or wrap them under a "data" key:    { "data": [ ... ] }
// Handle all forms.
$rawStudents = $allRawStudents;

// --- Clean & restructure data ---
$students = [];

foreach ($rawStudents as $student) {
    if (!is_array($student)) {
        continue;
    }

    $surname  = isset($student['sname']) ? $student['sname'] : '';
    $firstname = isset($student['fname']) ? $student['fname'] : '';
    $middlename = isset($student['mname']) ? $student['mname'] : '';

    $fullNameParts = array_filter([$surname, $firstname, $middlename]);
    $fullName = implode(' ', $fullNameParts);

    $students[] = [
        'id'           => isset($student['id']) ? $student['id'] : null,
        'matric_no'    => isset($student['regnum']) ? $student['regnum'] : (isset($student['matric_no']) ? $student['matric_no'] : null),
        'surname'      => $surname ?: null,
        'first_name'   => $firstname ?: null,
        'middle_name'  => $middlename ?: null,
        'name'         => $fullName ?: null,
        'department'   => isset($student['dept']) ? $student['dept'] : (isset($student['department']) ? $student['department'] : null),
        'faculty'      => isset($student['faculty']) ? $student['faculty'] : null,
        'programme'    => isset($student['prog']) ? $student['prog'] : null,
        'sex'          => isset($student['sex']) ? $student['sex'] : null,
        'dob'          => isset($student['dob']) ? $student['dob'] : null,
        'phone'        => isset($student['tel']) ? $student['tel'] : null,
        'email'        => isset($student['email']) ? $student['email'] : null,
        'address'      => isset($student['ad']) ? $student['ad'] : null,
        'nok'          => isset($student['k1nam']) ? $student['k1nam'] : null,
        'nok_phone'    => isset($student['k1tel']) ? $student['k1tel'] : null,
        // Portal may use either "pass_url" or "pass" for the passport field
        'passport_url' => isset($student['pass_url']) ? $student['pass_url'] : (isset($student['pass']) ? $student['pass'] : null),
    ];
}

// --- Final JSON response ---
echo json_encode([
    'status' => 'success',
    'page'   => $page,
    'limit'  => $limit,
    'count'  => count($students),
    'data'   => $students,
]);
