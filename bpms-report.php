<?php 
//session_start();
//error_reporting(0);

require_once 'dbconfig.php';

//require_once '../mail/mailer.php';

require_once '../bpms/bpms-functions.php';

include('../bpms/bpms-dbconnect.php');

// Also load the clinic application DB so we can update local records
// (e.g. pharmacy_order status) when BPMS confirms a payment.
@include_once 'assets/inc/config.php';

//echo 'dlfjd';
//print_r($_GET);
//echo strlen($_GET['request_id']);
if(isset($_GET['request_id']) && (strlen($_GET['request_id']) > 5))
{
	//echo '<br />tracker';
	 $transid = trim(($_GET['request_id']));
} else if (  isset($_GET['transid']) && (strlen($_GET['transid']) > 5)) 
{
	$transid = trim(($_GET['transid']));
}

//print_r($pdetails);



	//echo "SELECT * FROM transactions WHERE transid = '$transid'";
	/*$trans_search = $bpms->query("SELECT * FROM transactions WHERE transid = '$transid' ");
		//echo 'AFTER QUERY';
	if (!$trans_search)
	{
		die($bpms->errorInfo().' CHK_VAL_RPT_FAILD');
	}
	//echo ' numb is '.$trans_search->rowCount();
	if ($trans_search->rowCount() == 1)
		{
			$pdetails = $trans_search->fetch(PDO::FETCH_ASSOC);
			
	//		print_r($pdetails);
			$transid = $pdetails['transid'];
		$merchantid = $pdetails['merchant_id'];
	$hash = $pdetails['hash'];
	$hash_type = $pdetails['hash_type'];
			$status_code = $pdetails['status_code'];
		} */
else{
	die("ERROR: Missing transaction details!");
}

if (isset($transid) && (strlen($transid) > 3))
{
	$prow = checkBPMSPayment($transid, $merchant_id, $public_key, $queryPaymentGateway);
$pdetails = $prow;
} else 
{
	die("ERROR: ".$transid." Invalid Transid ");
}

//$prow = checkBPMSPayment($transid, $merchantid, $public_key, $privateKey, $hash, $hash_type, $pdetails,  $queryPaymentGateway);
//echo 'affter check';

/*echo 'REPORT IS <br />';
print_r($report);
echo '<br />pdetails is <br />';
print_r($pdetails);*/
//print_r($prow);

$title = $prow['transid'].' BPMS Payment Gateway Report';
$pagecode  = 'Online Payment'; 

//require 'head.php';
//require_once '../my-functions.php';

/*check_cand_authentication();
$jamb = $_SESSION['ugap'];
$regnum = $jamb;

$query = $putme->query("select * from register where regnum = '$jamb' and session = '$session'");
if (!$query)
{
	die($putme->error.' REG_SEL_ER');
}
if ($query->num_rows < 1)
{
	?>
	<script language="javascript">alert("ERROR SETTING RECORDS");document.location = document.referrer; </script>
	<?php exit;
} else 
{
	$candid = $query->fetch_array();
	$session = $candid['session'];
	
}
*/


//require the files
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <link href="style.css" rel="stylesheet" type="text/css">
<title><?PHP

	echo $title; ?></title>
</head>

<body onLoad="window.print()">

<?php $report = '<table width="800px" border="0" cellpadding="5" cellspacing="5"  background="../images/oou.png" align="center" class="tabl">
  <tr>
    <td colspan="4" align="center" class="txtbold"><img src="../images/newlogo2.jpg" width="100%"/></td>
  </tr>
  <tr>
    <td align="center" class="txtbold"><h4>&nbsp;</h4></td>
    <td colspan="3" align="center" class="txtbold"><strong>'.strtoupper($title).'</strong></td>
  </tr>
 <tr>
 
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Transaction ID</strong></td>
    <td colspan="2" align="left">'.$prow['transid'].'</td>
  </tr>
  <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Product ID</strong></td>
    <td colspan="2" align="left">'.$prow['product_id'].'</td>
  </tr>
  <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Matric/Reg. No.</strong></td>
    <td colspan="2" align="left">'.$prow['regnum'].'</td>
  </tr>
 
  <tr>
    <td colspan="2" align="right" bordercolor="#0000FF"><strong>Name</strong></td>
    <td colspan="2" align="left">'.$prow['sname'].' '.$prow['fname'].' '.$prow['mname'].'</td>
  </tr>
  <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Email Address</strong></td>
    <td colspan="2" align="left">'.$prow['customer_email'].'</td>
  </tr>
 <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Session/Level</strong></td>
    <td colspan="2" align="left">'.$prow['session'].'</td>
  </tr>
  <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Level</strong></td>
    <td colspan="2" align="left">'.$prow['level'].'</td>
  </tr>
  
  <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Programme</strong></td>
    <td colspan="2" align="left">'.$prow['prog'].'</td>
  </tr>
  <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Amount Paid</strong></td>
    <td colspan="2" align="left">&#8358; '.number_format($prow['amount_paid'], 2).'</td>
  </tr>
  <tr>
    <td align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Status (Code)</strong></td>
    <td colspan="2" align="left">'.$prow['status_desc'].' ('.$prow['status_code'].')'.'</td>
  </tr>
    
  <tr>
    <td colspan="2" align="right" bordercolor="#0000FF"><strong>Referrer</strong></td>
    <td colspan="2" align="left">'.$prow['referrer_url'].'</td>
  </tr>
  <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Description</strong></td>
    <td colspan="2" align="left">'.$prow['product_desc'].'</td>
  </tr>
  <tr>
    <td width="14%" align="center" bordercolor="#0000FF">&nbsp;</td>
    <td align="right" class="txtbold"><strong>Transaction Date</strong></td>
    <td colspan="2" align="left">'. $prow['trans_date'].'</td>
  </tr>
 ';  
	sendsmptmail('portal@oouagoiwoye.edu.ng', $prow['customer_email'], $prow['sname'].' '.$prow['fname'].' '.$prow['mname'], "#".$transid." PAYMENT REPORT", $report);
	echo $report;
  ?>

 
  
  <?php 
	if (isset($_SESSION['unam']) && (($_SESSION['mod'] == 'admin') || $_SESSION['mod'] == 'bus'))
	{ 
  ?><tr class="hidden-print">
    <td colspan="2" align="right" bordercolor="#0000FF"><strong>RAW RESPONSE</strong></td>
    <td colspan="2" align="left"><span class="hidden-print"><?php print_r(unserialize($prow['response'])) ?> </span></td>
  </tr>
 <?php  } 
  
//  $report .= '<tr></tr></table>';
	
	
  ?>
  <?php
  // If this transaction came from the clinic integration and BPMS has approved it,
  // update local clinic tables accordingly (e.g. mark pharmacy orders as Paid).
  if (isset($prow['status_desc']) && $prow['status_desc'] === 'APPROVED' && isset($prow['product_desc'])) {
      $parts = explode('|', $prow['product_desc']);
      // product_desc = regnum|level|prog|session|revcode|head
      if (count($parts) >= 3) {
          $regnum = $parts[0];
          $prog   = $parts[2];
          if (isset($mysqli) && $mysqli instanceof mysqli) {
              // Pharmacy orders use prog = CLINIC-PHARMACY and regnum = trackid
              if ($prog === 'CLINIC-PHARMACY' && !empty($regnum)) {
                  $trackid = $mysqli->real_escape_string($regnum);
                  $mysqli->query("UPDATE pharmacy_order SET status='Paid' WHERE trackid='".$trackid."'");
              }
          }
      }
  }
  ?>
  <tr><td colspan="4" align="center"><a href="<?php  if (isset($_SESSION['invid']) && ($_SESSION['invid'] == $prow['transid']) && ($prow['status_desc'] == 'APPROVED')) { echo 'payment-receipt.php'; $t = 'PRINT RECEIPT';} else { echo 'index.php'; $t = 'BACK TO HOME PAGE';} ?>" class="btn btn-success"> <?php echo $t;?> </a></td></tr>
	

    <td colspan="4" align="center"><?php 
	include("../foot.php");	
  $bpms->query("UPDATE transactions SET `check` = '1' WHERE transid = '$transid'");


	  //unset($_SESSION['ses']);
	  // unset($_SESSION['m']);
	   // unset($_SESSION['lev']);
		
		$bpms = NULL;
		?></td></tr>
</body>
</html>
