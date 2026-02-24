<?php
	function check_login()
	{
		if(strlen($_SESSION['doc_id'])==0)
			{
				$host = $_SERVER['HTTP_HOST'];
				$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
				$extra="admin_records.php";
				$_SESSION["doc_id"]="";
				header("Location: http://$host$uri/$extra");
			}
	}
	function authorize(){
//session_start();
if (!isset($_SESSION['doc_number'])) {
	header("location: index.php");
	echo "<h1>You are not Authorized to view this page. Please go and login.</h1>";
		
}
else{
	$username = $_SESSION['doc_number'];
	
}
	// Restrict Vice Chancellor to report/view-only pages
	if (isset($_SESSION['doc_dept']) && strtolower(trim($_SESSION['doc_dept'])) === 'vice chancellor') {
		$script = basename($_SERVER['PHP_SELF']);
		// whitelist: pages vc can access (reports and VC dashboard)
		$allowed = [
			'vc_dashboard.php',
			'visitdate.php',
			'visitdate_report.php',
			'storeview_report.php',
			'storemovement_report.php',
			'dailystock.php',
			'dailystock_report.php',
			'pharmacy_report.php',
			'phardailystock_report.php',
			'storestockview_report.php',
			'stock_report.php',
			'total_registered.php',
			'current_admitted.php',
		];
		// allow any file that includes 'report' in its name as well
		if (!(in_array($script, $allowed) || stripos($script, 'report') !== false || $script === 'vc_dashboard.php')) {
			$host = $_SERVER['HTTP_HOST'];
			$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			header("Location: http://$host$uri/vc_dashboard.php");
			exit;
		}
	}

/*if (isset($_GET['logout'])){
	$logout = $_GET['logout'];
	if ($logout == "true"){
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		unset($_SESSION['admin_name']);
	session_destroy();
	header("location: index.php");
	}
}*/

}
?>
