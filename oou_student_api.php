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
define('API_KEY', 'my_secure_hospital_key');

// Optionally pull in app config (DB, sessions, etc.)
// require_once __DIR__ . '/assets/inc/config.php';

// --- Simple API key protection ---
$headers = function_exists('getallheaders') ? getallheaders() : [];

$authHeader = '';
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
} elseif (isset($headers['authorization'])) { // some servers normalise header names
    $authHeader = $headers['authorization'];
}

if ($authHeader !== API_KEY) {
    http_response_code(401);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized',
    ]);
    exit;
}

// --- Input parameters ---
$type  = isset($_GET['type']) ? trim($_GET['type']) : 'UG';
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

// --- Build external API query ---
$queryParams = [
    'data'      => $type,
    'page'      => $page,
    'page_size' => $limit,
    'username'  => OOU_USERNAME,
    'password'  => OOU_PASSWORD,
];

// Pass through regnum filter if provided
if ($regnum !== '') {
    $queryParams['regnum'] = $regnum;
}

$query = http_build_query($queryParams);
$url   = OOU_API_URL . '?' . $query;

// --- Fetch data from external API ---
$context = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'timeout' => 20,
        'header'  => "User-Agent: HMS-OOU-Proxy\r\n",
    ],
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Failed to reach external OOU API',
    ]);
    exit;
}

$data = json_decode($response, true);
if (!is_array($data)) {
    http_response_code(502);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid response from external OOU API',
    ]);
    exit;
}

// External API may either return an array of students directly
// or wrap them under a "data" key; handle both forms.
$rawStudents = [];
if (isset($data['data']) && is_array($data['data'])) {
    $rawStudents = $data['data'];
} elseif (array_keys($data) === range(0, count($data) - 1)) {
    // numeric keys -> treat as list
    $rawStudents = $data;
}

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
        'passport_url' => isset($student['pass_url']) ? $student['pass_url'] : null,
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
