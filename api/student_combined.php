<?php
// API: Combined lookup that returns both student biodata (student portal)
// and medical information (med_data) for a given regnum.
// URL example:
//   http://your-host/clinic/api/student_combined.php?regnum=35307439DE

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

// 1) Fetch student data
$student = null;
$mysqliStudent = api_get_connection($API_STUDENT_DB);

// Use actual students table schema (same as in student_info.php)
$sqlStudent = "SELECT sn, regnum, jamb, sname, fname, mname, state, lga, mode, level, spill, sex, age,
                      faculty, dept, course, prog, pcode, ocode, jscore, cid, adm_year, grad_year,
                      cgpa, result, alum, nysc_batch, nysc_mod, nysc_dt, juni, jmsg, jcourse, jname,
                      putmestat, pass, jamb_letter, verif, verifdt, session, moved, sexchk,
                      wema_acc_number, wema_acc_status, created_at, updated_at
               FROM students
               WHERE regnum = ?
               LIMIT 1";

if ($stmt = $mysqliStudent->prepare($sqlStudent)) {
    $stmt->bind_param('s', $regnum);
    $stmt->execute();
    $res = $stmt->get_result();
    $student = $res->fetch_assoc();
    $stmt->close();
}
$mysqliStudent->close();

// 2) Fetch medical data
$medical = null;
$mysqliMed = api_get_connection($API_MEDICAL_DB);

$sqlMed = "SELECT regnum, med_id, tribe, medstat, clinic_dt, created_at, updated_at
           FROM med_data
           WHERE regnum = ?
           LIMIT 1";

if ($stmt2 = $mysqliMed->prepare($sqlMed)) {
    $stmt2->bind_param('s', $regnum);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $medical = $res2->fetch_assoc();
    $stmt2->close();
}
$mysqliMed->close();

// Build response
api_send_json(array(
    'success' => true,
    'regnum'  => $regnum,
    'student' => $student,   // may be null if not found
    'medical' => $medical,   // may be null if not found
));

?>
