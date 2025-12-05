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
