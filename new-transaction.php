<?php
// New clinic bridge: this local new-transaction.php no longer generates
// invoices itself. It simply hands control off to the central BPMS portal.

session_start();

// Preserve any incoming query string when redirecting.
$query  = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
$target = 'https://payments.oouagoiwoye.edu.ng/new-transaction.php';
if ($query !== '') {
    $target .= '?' . $query;
}

header('Location: ' . $target);
exit;
