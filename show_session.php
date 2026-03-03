<?php
// Temporary debugging helper — remove after use
session_start();
header('Content-Type: text/html; charset=utf-8');
echo '<h3>Session contents</h3>';
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
echo '<p>Remove this file from the server after use to avoid leaking session info.</p>';
?>