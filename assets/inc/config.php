<?php
$dbuser="root";
$dbpass="";
$host="localhost";
$db="Hospital";
$mysqli = new mysqli($host, $dbuser, $dbpass, $db);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}
?>
