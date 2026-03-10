<style type="text/css" >
// Print utilities
.visible-print { display: none !important; }
.hidden-print { }
@media print {
.visible-print { display: inherit !important; }
.hidden-print { display: none !important; }
}
</style><?php
//error_reporting(0);
session_start();
include("dbconfig.php"); 
require_once'../bpms/bpms-dbconnect.php';
require_once '../bpms/bpms-functions.php';
include 'num2word.php';
if (isset($_SESSION['invid'])){
	$transid = $_SESSION['invid'];
} else if ($_GET['id'])
	
{
	$transid = $bpms->quote(trim($_GET['id']));
}

$trans_search = $bpms->query("SELECT * FROM transactions WHERE transid = '$transid'");
		//echo 'AFTER QUERY';
	if (!$trans_search)
	{
		die($bpms->errorInfo().' CHK_VAL_RPT_FAILD');
	}
	//echo ' numb is '.$trans_search->rowCount();
	if ($trans_search->rowCount() == 1)
		{
			?>
			<script language="javascript">
alert("INVOICE PREVIOUSLY GENERATED\NGO BACK TO HOME PAGE TO GENERATE A NEW INVOICE OR TRACK STATUS OF PREVIOUS PAYMENT");
				document.location = 'bpms-report.php?request_id=<?php echo $transid?>&mode=track'
				
</script>
			<?php
			
		}

$q = $pydb->query("select * from invoices where transid = '$transid'");

    
  
if (!$q)
{
	die($pydb->errorInfo().' INV_DLD_DLDE');
}

if ($q->rowCount() > 0)
{
	$inv = $q->fetch(PDO::FETCH_ASSOC);
	
	/*$rq = $pydb->query("select * from revenues where sn = '{$inv['purpose']}'");
	if (!$rq)
	{
		die($pydb->errorInfo().'chk_rev_r');
	}*/
		
} else {
	?>
<script language="javascript">
alert("SORRY TRANSACTION <?php echo $transid ?> WAS NOT FOUND");
	document.location = document.referrer;
</script>
	<?php
		exit;
}
$title = "#". $transid." INVOICE PAYMENT PROFILE";
//$title = "STUDENTS: Payment Invoice";
//$page = "payhistory";
//$pagetitle = "Students";

//include("../head.php");

if (empty($inv['regnum']))
{
	$regnum = 'NA';
} else {
	$regnum = $inv['regnum'];
}
	$email = $inv['email'];

	$tel = $inv['tel'];



	$session = $inv['session'];
	$level = $inv['level'];











	
	//END OF LATE PAYMENT ACTIVITIES
	 
	 

///CLOSURE OF PAYMENT



			
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <link href="style.css" rel="stylesheet" type="text/css">
<title><?PHP echo $title?></title>
</head>

<body>
<table width="800px" border="0" cellpadding="5" cellspacing="5"  background="images/oou.png" align="center" class="tabl">
  <tr>
    <td colspan="4" align="center" class="txtbold"><img src="images/newlogo2.jpg" width="100%"/></td>
  </tr>
  <tr>
    <td colspan="4" align="center" valign="middle" class="txtbold"> <?php echo $title ?></td>
  </tr>
  
      <?php
		// $transid = generateTransId(10);
		 //echo $transid;
		 
		 
	
	$revcode = $inv['revcode'];
	$amount = $inv['amount'];
    $product_id = $revcode;
    
    if (empty($product_id) || strlen($product_id) < 3) {
      ?>
      <script language="javascript">
      alert("INVALID INVOICE, UNABLE TO VALIDATE REVENUE CODE\nPLEASE TRY AGAIN")
      document.location = document.referrer;
      </script>
      <?php
      exit;
    }
			$referrer_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$callback_url= "https://payments.oouagoiwoye.edu.ng/bpms-report.php";
		//MATRIC NUMBER|LEVEL|DEPARTMENT|SESSION|OTHERS
			$product_desc = $regnum.'|'.$level.'|'.$inv['prog'].'|'.$session.'|'.$revcode.'|'.$inv['head']; 
			//$customer_email = 'banjotobi@gmail.com';
					   // Matric No|Session | Level | Course 
					   $referrer_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//$callback_url= "https://portal.oouagoiwoye.edu.ng/students/xpayment-report.php";
//$product_desc = 'SCHOOL FEES';
$customer_email = $email;
//$amount = $topay;
					// echo 'before';
//prePurchaseLogger($regnum, $sname, $fname, $mname, $level, $prog, $session, $portal, $product_id, $referrer_url, $callback_url, $product_desc, $amount, $currency, $email, $merchant_id, $hash_type, $public_key, $privateKey, $transid)
		//preBPMSPurchaseLogger($regnum, $sname, $fname, $mname, $level, $prog, $session, $portal, $product_id, $referrer_url, $callback_url, $product_desc, $amount, $currency, $email, $merchant_id, $hash_type, $public_key, $privateKey, $transid, $tel, $paymentType, $paymentGateway)
$details = preBPMSPurchaseLogger($regnum, $inv['sname'], $inv['fname'], $inv['mname'], $level, $inv['prog'], $session, 'PYM', $product_id, $referrer_url, $callback_url, $product_desc, $amount, $currency, $email, $merchant_id, $hash_type, $public_key, $privateKey, $transid, $tel, 'WEB', $paymentGateway);
	
	
	//preBPMSPurchaseLogger($regnum, $rw['sname'], $rw['fname'], $rw['mname'], $level, $rw['prog'], $session, 'UGSTD', $product_id, $referrer_url, $callback_url, $product_desc, $amount, $currency, $email, $merchant_id, $hash_type, $public_key, $privateKey, $transid, $tel, 'WEB', $paymentGateway);
					  // echo 'after';
//$xpay = prePurchaseLogger($regnum, $candid['mode'], $candid['session'], 'PUTME', "HLC018",  $referrer_url, $callback_url, $product_desc, $amount, $currency, $candid['mail'], $merchant_id, $public_key, $transid);
				//print_r($details)	   ;
if ($details == 'FAILED')
{
	?>
      <script language="javascript">
						 alert("UNABLE TO VALIDATE TRANSACTION\nPLEASE TRY AGAIN LATER");
					   document.location = document.referrer; </script>
      <?php
				 exit;
}
		// echo $details['prn'];
		 $body = '<table width="800px" border="0" cellpadding="5" cellspacing="5"  background="images/oou.png" align="center" class="tabl">
  <tr>
    <td colspan="4" align="center" class="txtbold"><img src="images/newlogo2.jpg" width="100%"/></td>
  </tr>
  <tr>
    <td colspan="4" align="center" valign="middle" class="txtbold">'.$title.'</td>
  </tr>
  
   <tr>
     <th align="right" nowrap="nowrap"><span class="txtbold">Transaction ID</span></th>
     <td colspan="6" align="left" valign="middle">'.$transid.'</td>
   </tr>
   <tr>
                   <th align="right"><span class="txtbold">Reg. No</span></th>
                   <td colspan="6" align="left" valign="middle">'. $inv['regnum'].'</td>
  </tr>
  <tr>
    <td align="right" nowrap="nowrap" class="txtbold">Name</td>
    <td colspan="3" align="left">'.$inv['sname'].' '.$inv['fname'].' '.$inv['mname'].'</td>
  </tr>
  <tr>
    <td align="right" class="txtbold"> Session</td>
    <td colspan="3" align="left">'.$inv['session'].'</td>
  </tr>
  <tr>
    <td align="right" class="txtbold"> Level</td>
    <td colspan="3" align="left">'. $level.'</td>
  </tr>
  <tr>
    <td align="right"><span class="txtbold">Programme</span></td>
    <td colspan="2" align="left">'. $inv['prog'].'</td>
  </tr>
   <tr>
    <td align="right"><span class="txtbold">Telephone</span></td>
    <td colspan="2" align="left">'.$inv['tel'].'</td>
  </tr>
    <tr>
    <td align="right"><span class="txtbold">Email</span></td>
    <td colspan="2" align="left">'. $inv['email'].'</td>
    <tr>
    <td align="right"><span class="txtbold">Revenue Code</span></td>
    <td colspan="2" align="left">'.$inv['revcode'].'</td>
  </tr>
   <tr>
    <td align="right" nowrap="nowrap"><span class="txtbold">Revenue Head</span></td>
    <td colspan="2" align="left">'.$inv['head'].'</td>
  </tr>
  <tr>
    <td align="right" class="txtbold">Remarks</td>
    <td colspan="3" align="left">'. $inv['remarks'].'</td>
  </tr>
  <tr>
    <td colspan="4" align="center" class="txtbold">BREAKDOWN OF FEES</td>
  </tr>
  <tr>
    <td align="right" class="txtbold">&nbsp;</td>
    <td align="right" nowrap="nowrap"><span class="txtbold"> Total Amount Payable</span></td>
    <td colspan="2" align="right">&#8358;
    '.number_format($amount, 2).'</td>
  </tr>
  
 
  <tr>
    <td class="txtbold" align="right">&nbsp;</td>
    <td align="right" class="txtbold">In words</td>
    <td colspan="2" align="left" class="txtbold"><p align="justify">'. strtoupper(convert_number_to_words($amount)).' NAIRA ONLY</p></td>
  </tr>
  <tr>
    <td class="txtbold" align="right">&nbsp;</td>
    <td colspan="2" align="left" class="txtbold">&nbsp;</td>
    <td width="18%">&nbsp;</td>
  </tr></table>';
	
	$msg = 'Dear '.$inv['fname'].'<br />
	This is to inform you that you have successfully generated INVOICE with details below:<br />'.$body.'<br />
<a href="https://payments.oouagoiwoye.edu.ng/payment-invoice.php?id='.$transid.'">VIEW INVOICE #'.$transid.'</a><br />
<br />
OOU PORTAL';
	require_once('../mail/mailer.php');
	
	sendsmptmail('portal@oouagoiwoye.edu.ng', $inv['email'], $inv['sname'].' '.$inv['fname'].' '.$inv['mname'], 'INVOICE #'.$transid.' SUCCESSFULLY GENERATED' , $msg);

		 ?>
    
   <tr>
     <th align="right" nowrap="nowrap"><span class="txtbold">Transaction ID</span></th>
     <td colspan="6" align="left" valign="middle"><?php 
					   
					    echo $transid ;?></td>
   </tr>
   <tr>
                   <th align="right"><span class="txtbold">Reg. No</span></th>
                   <td colspan="6" align="left" valign="middle"><?php echo $inv['regnum']?></td>
  </tr>
  <tr>
    <td align="right" nowrap="nowrap" class="txtbold">Name</td>
    <td colspan="3" align="left"><?php echo $inv['sname'].' '.$inv['fname'].' '.$inv['mname'];?></td>
  </tr>
  <tr>
    <td align="right" class="txtbold"> Session</td>
    <td colspan="3" align="left"><?php echo $inv['session']; ?></td>
  </tr>
  <tr>
    <td align="right" class="txtbold"> Level</td>
    <td colspan="3" align="left"><?php echo $level; ?></td>
  </tr>
  <tr>
    <td align="right"><span class="txtbold">Programme</span></td>
    <td colspan="2" align="left"><?php echo $inv['prog']; ?></td>
  </tr>
   <tr>
    <td align="right"><span class="txtbold">Telephone</span></td>
    <td colspan="2" align="left"><?php echo $inv['tel']; ?></td>
  </tr>
    <tr>
    <td align="right"><span class="txtbold">Email</span></td>
    <td colspan="2" align="left"><?php echo $inv['email']; ?></td>
    <tr>
    <td align="right"><span class="txtbold">Revenue Code</span></td>
    <td colspan="2" align="left"><?php echo $inv['revcode']; ?></td>
  </tr>
   <tr>
    <td align="right" nowrap="nowrap"><span class="txtbold">Revenue Head</span></td>
    <td colspan="2" align="left"><?php echo $inv['head']; ?></td>
  </tr>
  <tr>
    <td align="right" class="txtbold">Remarks</td>
    <td colspan="3" align="left"><?php echo $inv['remarks']; ?></td>
  </tr>
  <tr>
    <td colspan="4" align="center" class="txtbold">BREAKDOWN OF FEES</td>
  </tr>
  <?php
	/*
	$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
	
	*/
	
  ?>
  
  <tr>
    <td align="right" class="txtbold">&nbsp;</td>
    <td align="right" nowrap="nowrap"><span class="txtbold"> Total Amount Payable</span></td>
    <td colspan="2" align="right">&#8358;
    <?php  echo number_format($amount, 2) ?></td>
  </tr>
  
 
  <tr>
    <td class="txtbold" align="right">&nbsp;</td>
    <td align="right" class="txtbold">In words</td>
    <td colspan="2" align="left" class="txtbold"><p align="justify"><?php echo strtoupper(convert_number_to_words($amount)).' NAIRA ONLY' ?></p></td>
  </tr>
  <tr>
    <td class="txtbold" align="right">&nbsp;</td>
    <td colspan="2" align="left" class="txtbold">&nbsp;</td>
    <td width="18%">&nbsp;</td>
  </tr>
  <tr class="hidden-print">
    <td class="txtbold" align="right">&nbsp;</td>
    <td align="center" class="txtbold"><a href="#" class="btn btn-info" onClick="window.print()">PRINT INVOICE</a></td>
    <td width="28%" align="center" class="txtbold">
                    <form id="form1" name="form1" method="post" action="<?php echo $paymentGateway;?>">
						
						<input name="billed_amount" type="hidden" value="<?php echo $details['amount_due']; ?>"/>
                     <input name="name" type="hidden" value="<?php echo $fullName =  strtoupper($inv['sname']).' '.ucwords(strtolower($inv['fname'])).' '.ucwords(strtolower($inv['mname'])); ?>"/>
		
                     <input name="school_code" type="hidden" value="<?php echo $details['merchant_id']; ?>"/>
                     <input name="date" type="hidden" value="<?php 
															  $h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
$hm = $h * 60;
$ms = $hm * 60;
echo $regdate = gmdate("d/m/Y g:i:s A", time()+($ms));
															// echo $details['merchant_id']; ?>"/>
                     <input name="bill_description" type="hidden" value="<?php  echo $regnum." | ".$fullName." | ".$inv['head']."  | ".$level." Level" ?>"/>
                    
                     <input name="customer_phone" type="hidden" value="<?php echo $tel; ?>"/>
                     <input name="customer_id" type="hidden" value="<?php echo $regnum; ?>"/>
                     <input name="customer_first_name" type="hidden" value="<?php echo $inv['fname']; ?>"/>
                     <input name="customer_last_name" type="hidden" value="<?php echo $inv['sname']; ?>"/>
                     <input name="customer_address" type="hidden" value="<?php echo 'NAN' ?>"/>
                     <input name="customer_fname" type="hidden" value="<?php echo $inv['fname']; ?>"/>
                     <input name="public_key" type="hidden" value="<?php echo $public_key ?>"/>
                     <input name="request_id" type="hidden" value="<?php echo $details['transid'] ?>"/>
                     <input name="revenue_code" type="hidden" value="<?php echo $details['product_id']  ?>"/>
                     <input name="currency" type="hidden" value="<?php echo $details['currency'] ?>"/>
                     <input name="callback_url" type="hidden" value="<?php echo $details['callback_url']  ?>"/>
                     <input name="product-desc" type="hidden" value="<?php echo $details['product_desc'] ?>"/>
                     <input name="customer_email" type="hidden" value="<?php echo $details['customer_email']; ?>"/>
                     <input name="hash_type" type="hidden" value="<?php echo $details['hash_type'];  ?>"/>
                     <input name="hash" type="hidden" value="<?php echo $details['hash']; ?>"/>
		
						
						
						
						
						
                    <!--<input name="amount" type="hidden" value="<?php echo $details['amount_due']; ?>"/>
                     <input name="merchant-id" type="hidden" value="<?php echo $details['merchant_id']; ?>"/>
                     <input name="public-key" type="hidden" value="<?php echo $public_key ?>"/>
                     <input name="trans-id" type="hidden" value="<?php echo $details['transid'] ?>"/>
                     <input name="product-id" type="hidden" value="<?php echo $details['product_id']  ?>"/>
                     <input name="currency" type="hidden" value="<?php echo $details['currency'] ?>"/>
                     <input name="callback-url" type="hidden" value="<?php echo $details['callback_url']  ?>"/>
                     <input name="product-desc" type="hidden" value="<?php echo $details['product_desc'] ?>"/>
                     <input name="customer-email" type="hidden" value="<?php echo $details['customer_email']; ?>"/>
                     <input name="hash-type" type="hidden" value="<?php echo $details['hash_type'];  ?>"/>
                     <input name="hash" type="hidden" value="<?php echo $details['hash']; ?>"/>-->
  <!--    <input name="tel" type="hidden" value="<?php echo $tel; ?>"/><input name="email" type="hidden" value="<?php echo $email; ?>"/>
      <input name="purpose" type="hidden" id="purpose" value="SCHOOL FEES"/> -->     <button  class="btn btn-success" style="font-size:18px"><i class="fa fa-cc-mastercard"></i>PROCEED TO WEB PAYMENT GATEWAY </button></form></td>
    <td>&nbsp;</td>
  </tr>
    <?php 
	
				/*if ($late == 1)
				{
				?>
                 <tr>
                  <td colspan="4" align="center" valign="middle" class="txtbold headmark">  PLEASE NOTE THAT THE SUM OF &#8358; <?PHP echo number_format($latedue, 2) ?> HAS BEEN ADDED TO YOUR BILL AS THE PENALTY CHARGE FOR LATE PAYMENT OF SCHOOL FEES FOR <?php echo $rw5['session']?> ACADEMIC SESSION</td>
                </tr>
                <?php } */?>
  <tr>
    <td colspan="4" align="justify"><?PHP 
	include("foot.php");
	
	  //unset($_SESSION['ses']);
	  // unset($_SESSION['m']);
	   // unset($_SESSION['lev']);
		?></td>
  </tr>
</table>
</body>
</html>