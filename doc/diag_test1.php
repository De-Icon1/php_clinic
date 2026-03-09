<?php
// diag_test.php — temporary. Delete after use.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Convert warnings/notices to exceptions for clearer output
set_error_handler(function($severity, $message, $file, $line) {
  throw new ErrorException($message, 0, $severity, $file, $line);
});

echo "diag_test: start\n";

// Throw a test exception so we know display works
throw new Exception("DIAG_TEST_EXCEPTION");

// (script will stop here)
?>