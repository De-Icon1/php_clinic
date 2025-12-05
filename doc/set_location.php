<?php
session_start();
include('assets/inc/config.php');

if(isset($_POST['pharmacy_location'])) {

    $_SESSION['pharmacy_location_id'] = $_POST['pharmacy_location'];

    $locid = $_POST['pharmacy_location'];
    $q = $mysqli->prepare("SELECT name FROM pharmacy_locations WHERE id=?");
    $q->bind_param("i", $locid);
    $q->execute();
    $q->bind_result($locname);
    $q->fetch();

    $_SESSION['pharmacy_location_name'] = $locname;
}

header("Location: his_doc_dashboard.php");
exit;
?>