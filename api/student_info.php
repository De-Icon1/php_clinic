<?php
// API: Fetch student biodata from the student portal database by regnum.
// URL example:
//   http://your-host/clinic/api/student_info.php?regnum=35307439DE

require_once __DIR__ . '/config.php';

$regnum = isset($_GET['regnum']) ? trim($_GET['regnum']) : '';
if ($regnum === '' && isset($_POST['regnum'])) {
    $regnum = trim($_POST['regnum']);
}

if ($regnum === '') {
    api_send_json(array(
        'success' => false,
        'error'   => 'Missing regnum parameter',
    ), 400);
}

// Connect to student portal DB
$mysqli = api_get_connection($API_STUDENT_DB);

// Real students table schema (from portal):
// SELECT sn, regnum, jamb, sname, fname, mname, state, lga, mode, level, spill, sex, age,
//        faculty, dept, course, prog, pcode, ocode, jscore, cid, adm_year, grad_year,
//        cgpa, result, alum, nysc_batch, nysc_mod, nysc_dt, juni, jmsg, jcourse, jname,
//        putmestat, pass, jamb_letter, verif, verifdt, session, moved, sexchk,
//        wema_acc_number, wema_acc_status, created_at, updated_at FROM students

$sql = "SELECT sn, regnum, jamb, sname, fname, mname, state, lga, mode, level, spill, sex, age,
           faculty, dept, course, prog, pcode, ocode, jscore, cid, adm_year, grad_year,
           cgpa, result, alum, nysc_batch, nysc_mod, nysc_dt, juni, jmsg, jcourse, jname,
           putmestat, pass, jamb_letter, verif, verifdt, session, moved, sexchk,
           wema_acc_number, wema_acc_status, created_at, updated_at
    FROM students
    WHERE regnum = ?
    LIMIT 1";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    api_send_json(array(
        'success' => false,
        'error'   => 'Failed to prepare student query',
    ), 500);
}

$stmt->bind_param('s', $regnum);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
$mysqli->close();

if (!$student) {
    api_send_json(array(
        'success' => false,
        'error'   => 'Student not found',
    ), 404);
}

api_send_json(array(
    'success' => true,
    'data'    => $student,
));

?>
