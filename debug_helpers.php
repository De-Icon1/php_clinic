<?php
// Reusable lightweight SQL debug helpers
// Target: PHP 5.6+ (no strict typing)

if (!function_exists('debug_sql_block')) {
    /**
     * Print a small debug block showing current filter + SQL + row count.
     *
     * Usage pattern inside a page:
     *   require_once 'debug_helpers.php';
     *   ... build $status, $campus_id, $hascamp, $ret, execute $stmt ...
     *   $res = isset($stmt) ? $stmt->get_result() : null;
     *   debug_sql_block(isset($_GET['debug_sql']) ? $_GET['debug_sql'] : null,
     *                   $status, isset($campus_id) ? $campus_id : null,
     *                   isset($hascamp) ? $hascamp : 0,
     *                   isset($ret) ? $ret : '',
     *                   $res);
     */
    function debug_sql_block($debugFlag, $status, $campus_id, $hascamp, $sql, $result)
    {
        if ($debugFlag != '1') {
            return; // Only show when debug_sql=1
        }

        $rows = ($result && method_exists($result, 'num_rows')) ? $result->num_rows : 0;

        echo '<pre style="background:#eef;padding:8px;margin-top:10px;">';
        echo 'DEBUG status=' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . "\n";
        echo 'DEBUG campus_id=' . ($campus_id !== null && $campus_id !== '' ? (int)$campus_id : 'NULL') . "\n";
        echo 'DEBUG hascamp=' . (int)$hascamp . "\n";
        echo 'DEBUG SQL=' . htmlspecialchars($sql, ENT_QUOTES, 'UTF-8') . "\n";
        echo 'DEBUG rows=' . (int)$rows . "\n";
        echo '</pre>';
    }
}

?>
