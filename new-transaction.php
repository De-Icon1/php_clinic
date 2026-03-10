
<?php
$title = "Generate Invoice";
session_start();
function test_input($data)
{

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysql_real_escape_string($data);

    return $data;
}



include("head.php");
include("../bpms/bpms-dbconnect.php");
require "../bpms/bpms-functions.php";
include("dbconfig.php");



if ($close != 0) {
    ?>
<script language="javascript">
alert("SORRY PAYMENTS HAS BEEN TEMPORARILY CLOSED");
</script>
	<?php exit;
}
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="javascript" type="text/javascript">
// Roshan's Ajax dropdown code with php
// This notice must stay intact for legal use
// Copyright reserved to Roshan Bhattarai - nepaliboy007@yahoo.com
// If you have any problem contact me at http://roshanbh.com.np
function getXMLHTTP() { //fuction to return the xml http object
		var xmlhttp=false;	
		try{
			xmlhttp=new XMLHttpRequest();
		}
		catch(e)	{		
			try{			
				xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e){
				try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				}
				catch(e1){
					xmlhttp=false;
				}
			}
		}
		 	
		return xmlhttp;
    }
	
	function getState(countryId) {		
		
		var strURL="prog.php?country="+countryId;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('statediv').innerHTML=req.responseText;						
					} else {
					alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}
	
	
	function getAmount(countryId) {		
		
		var strURL="amount.php?country="+countryId;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						document.getElementById('amntdiv').innerHTML=req.responseText;						
					} else {
					alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}


	function getPurpose(countryId) {		
		
		var strURL="purpose.php?country="+countryId;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('purposediv').innerHTML=req.responseText;						
					} else {
					alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}
	
	
	function getState2(countryId, stateID) {		
		
		var strURL="dept.php?country="+countryId+"&state="+stateID;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('statediv').innerHTML=req.responseText;						
					} else {
						//alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}


	
	////
	function getProg(progID) {		
		
		var strURL="prog.php?proga="+progID;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('citydiv').innerHTML=req.responseText;						
					} else {
						//alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}

function getProg2(progID, cid) {		
		
		var strURL="prog.php?proga="+progID+"&cid="+cid;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('citydiv').innerHTML=req.responseText;						
					} else {
						//alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}
	////
	
</script>

    <link href="style.css" rel="stylesheet" type="text/css">
    <link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css">
    <link href="../SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css">
    <script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <script src="../SpryAssets/SpryValidationTextarea.js" type="text/javascript"></script>
    <div class="row space30"> <!-- row 1 begins -->
      
            <div class="col-md-4">
              	<h2>&nbsp;</h2>
                <br /><br /><br />
                   <!--<table width="50%" border="0" cellpadding="5" cellspacing="5" align="right">
  <tr>
    <td align="right"><a  class="btn btn-success" href="biodata.php">Biodata</a></td>
  </tr>
  <tr>
    <td align="right"><a  class="btn btn-primary" href="edudata.php">Education</a></td>
  </tr>
  <tr>
    <td align="right"><a  class="btn btn-primary" href="preview.php">Preview</a></td>
  </tr>
  <tr>
  <?php if (isset($_SESSION['unam'])) {
      echo '<td align="right"><a  class="btn btn-primary" href="../manage/dashboard.php">Admin Dashboard</a></td>';
  } else {
      echo '<td align="right"><a  class="btn btn-primary" href="dashboard.php">Dashboard</a></td>';
  }

?>
  </tr>
</table>-->

<?php
/*
<ul class="progress-bar">
                <li><a class="btn-sm btn-success" href="#">Details &raquo;</a></li>
                <li> <a class="btn-sm btn-danger" href="#">Second &raquo;</a></li>
                <li><a class="btn-sm btn-success" href="#">Details &raquo;</a></li>
                </ul>
*/
?>
           	  
              
        </div>
        
            <div class="col-xs-12 col-sm-6 col-lg-8">
              	<h2 class="headmark">Generate New Invoice</h2>
              	<form action="" method="post" class="form-horizontal">
              	  <table width="93%" border="0" align="center" cellpadding="5">
              	    <tr>
              	      <th width="290" align="right" valign="middle" class="scoreentry">&nbsp;</th>
              	      <th colspan="2" align="left" valign="middle" class="headmark">DATA CAPTURE FORM</th>
           	        </tr>
              	    
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Matric/Jamb/Registration/Identity</strong></td>
              	      <td colspan="2" align="left" valign="middle">
                      <input name="regnum" type="text" required="required" class="form-control" id="regnum"  size="30" maxlength="25"  /> </td>
           	        </tr>
           	        
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Surname</strong></td>
              	      <td colspan="2" align="left" valign="middle"><input name="sname" type="text" required class="form-control" id="sname" value=""  /></td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>First Name</strong></td>
              	      <td colspan="2" align="left" valign="middle"><input name="fname" type="text" required  class="form-control" id="fname"  /></td>
           	        </tr>
           	        <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Middle Name</strong></td>
              	      <td colspan="2" align="left" valign="middle"><input name="mname" type="text" required class="form-control" id="mname" /></td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Level</strong></td>
              	      <td colspan="2" align="left"  valign="middle"><select name="level" id="level">
              	      
              	       
              	        <option value="NL">NO LEVEL</option>
              	        <option value="100">100</option>
              	        <option value="200">200</option>
              	        <option value="300">300</option>
              	        <option value="400">400</option>
              	        <option value="500">500</option>
              	        <option value="600">600</option>
              	        <option value="700">700</option>
              	        <option value="800">800</option>
              	        <option value="900">900</option>
                      </select></td>
           	        </tr>
           	        <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Mode</strong></td>
              	      <td colspan="2" align="left"  valign="middle"><select name="mode" id="mode" onchange=" getPurpose(this.value); getState(this.value)">
              	      
              	       
              	        <option value="NL">SELECT MODE</option>
              	        <option value="ALL">ALL PORTALS</option>
              	        <option value="MAIN UNI">MAIN UNI</option>
              	        <option value="CCED">CCED</option>
              	        <option value="PGS">PGS</option>
						<option value="ICT">ICT</option>
                      </select></td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Programme</strong></td>
              	      <td colspan="2" align="left" valign="middle">
              	      
              	      <div id="statediv">
      <select name="prog" id="prog" class="form-control"   >
        <option>Select Programme</option>
      </select>
      </div>
              	     <!-- <select name="prog" id="prog" style="max-width: 500px"  class="form-control">
             	        <option value="NP">NO PROGRAMME</option>
              	       
						  </select>--></td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Session</strong></td>
              	      <td colspan="2" align="left"  valign="middle"><select name="session" id="session">
              	        <option value="NL">NO SESSION</option>
              	        <?php
                        $est = 1986;
$cyez  = gmdate("Y") + 1;//$stated = '';
while ($cyez > $est) {
    $ncys = $cyez - 1;
    $ses_year =  $ncys."/".$cyez;
    $cyez = $ncys;

    echo '<option value="'.$ses_year.'">'.$ses_year.'</option>';

}
?>
						
           	          </select></td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Telephone No</strong></td>
              	      <td colspan="2" align="left" valign="middle"><span id="sprytextfield11">
              	        <input name="tel" type="text" required class="form-control" id="tel" value=""  size="30">
              	      </span></td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Email Address</strong></td>
              	      <td colspan="2" align="left" valign="middle"><span id="sprytextfield10">
              	        <input name="email" type="email" required  class="form-control" id="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$"  value="" />
              	      </span></td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Payment Purpose</strong></td>
              	      <td colspan="2" align="left"  valign="middle">
             	        
             	        
       <div id="purposediv">
      <select name="purpose" id="purpose" class="form-control"   >
        <option>Select Purpose</option>
      </select>
      </div>
            	        
             	        
             	        
<!--
             	        <select name="purpose" class="form-control" id="purpose">
              	        <option value="">SELECT PURPOSE</option>
              	        <?php

?>
           	          </select></td>
-->
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Amount</strong></td>
              	      <td colspan="2" align="left" valign="middle">
              	      <div id="amntdiv">
      <input name="amount" type="number" required class="form-control" id="amount" min="1" />
      </div>
              	      
              	      </td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle" class="txtbold"><strong>Remarks</strong></td>
              	      <td colspan="2" align="left" valign="middle">
                      <textarea name="remarks" id="remarks" class="form-control" ></textarea></td>
           	        </tr>
              	    <tr>
              	      <th align="right" valign="middle">&nbsp;</th>
              	      <td colspan="2" align="center" valign="top"><input name="generate" type="submit" class="linetext btn btn-success" id="generate" value="GENERATE INVOICE" /></td>
           	        </tr>
              	    <tr>
              	      <td align="right" valign="middle"></td>
              	      <td width="508" align="right" valign="middle">&nbsp;</td>
              	      <td width="79" align="right" valign="middle">&nbsp;</td>
           	        </tr>
           	      </table>
              	  <?php
 if (isset($_POST['generate'])) {
     //print_r($_POST);
     // exit;
     $regnum =  htmlentities(strtoupper($_POST['regnum']), ENT_QUOTES);
     $sname =  htmlentities(strtoupper($_POST['sname']), ENT_QUOTES);
     $fname  = htmlentities(ucwords($_POST['fname']), ENT_QUOTES);
     $mname = htmlentities(ucwords($_POST['mname']), ENT_QUOTES);
     $level = strtoupper($_POST['level']);
     $prog = strtoupper($_POST['prog']);
     $session = $_POST['session'];
     $tel = $_POST['tel'];
     $email = $_POST['email'];
     $purpose = $_POST['purpose'];

     $amount = $_POST['amount'];
     // echo 'before check';
     $w = $pydb->query("select * from revenues where sn = '$purpose'");
     if (!$w) {
         die($pydb->errorInfo().' ERR_REV_CHK');
     }
     //echo 'after rev_chk';
     if ($w->rowCount() == 1) {
         $rev = $w->fetch(PDO::FETCH_ASSOC);
         if (($rev['amount'] != null) && ($rev['amount'] > 0)) {
             $amount = $rev['amount'];
         }
     } else {

         ?>
		 <script language="javascript">
		alert("INVALID PURPOSE SELECTED <?php echo $purpose?>");
		</script>
		 <?php exit;

     }
     if (empty($purpose)) {
         ?>
		 <script language="javascript">
		alert("NO PURPOSE SELECTED");
		</script>
		 <?php exit;
     }
     if (empty($amount) || ($amount < 1)) {
         ?>
		 <script language="javascript">
			alert("INVALID AMOUNT SPECIFIED");		
			 //document.location
					</script>
		 <?php exit;
     }

     $remarks =  htmlentities($_POST['remarks'], ENT_QUOTES);
     //echo 'before';
     $invoiceid = generateBPMSTransId('INV');
     //$invoiceid = 'INV'.mt_rand(0,9).time();;
     //echo 'after gen';
     $_SESSION['invid'] = $invoiceid;
     //echo "insert into invoices (invoiceid, regnum, sname, fname, mname, level, prog, session, email, tel, purpose, amount, remarks) VALUES ('$invoiceid', $regnum', '$sname', '$fname', '$mname', '$level', '$prog', '$session', '$email', '$tel', '$purpose', '$amount', '$remarks')";
     //echo 'about to inserte';
     //echo "insert into invoices (invoiceid, regnum, sname, fname, mname, level, prog, session, email, tel, purpose, amount, remarks) VALUES ('$invoiceid', $regnum', '$sname', '$fname', '$mname', '$level', '$prog', '$session', '$email', '$tel', '$purpose', '$amount', '$remarks')";
     $q = $pydb->query("insert into invoices (transid, regnum, sname, fname, mname, level, prog, session, email, tel, purpose, revcode, head, amount, remarks) VALUES ('$invoiceid', '$regnum', '$sname', '$fname', '$mname', '$level', '$prog', '$session', '$email', '$tel', '$purpose', '{$rev['revcode']}', '{$rev['head']}', '$amount', '$remarks')");
     //echo 'after insert';

     if (!$q) {
         die($pydb->errorInfo().' INV_IN_FLD');
     }


     ?>
	 <script language="javascript">
					document.location = 'payment-invoice.php'
					</script>
	 <?php
     exit;
 }

?>
           	  </form>
              	<p>&nbsp;</p>
              	
        </div>
    </div> <!-- /row 1 -->
      
      <div class="row space30"> <!-- row 2 begins --></div> <!-- /row 2 -->

      <!-- Site footer -->
      
      <?php
      include("foot.php");
?>
      <script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "date", {format:"dd/mm/yyyy", useCharacterMasking:true});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "email");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3");
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4");
var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "phone_number", {format:"phone_custom", pattern:"0000-000-0000", useCharacterMasking:true});
var sprytextfield6 = new Spry.Widget.ValidationTextField("sprytextfield6");
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
var sprytextfield7 = new Spry.Widget.ValidationTextField("sprytextfield7");
var sprytextfield8 = new Spry.Widget.ValidationTextField("sprytextfield8", "phone_number", {format:"phone_custom", pattern:"0000-000-0000", useCharacterMasking:true});
var sprytextfield9 = new Spry.Widget.ValidationTextField("sprytextfield9", "email");
      </script>
