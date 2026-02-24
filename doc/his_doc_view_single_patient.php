<?php
  session_start();
  include('assets/inc/config.php');
  include('assets/inc/checklogin.php');
  check_login();

  $doc_id=$_SESSION['doc_id'];
  $campusid=$_SESSION['campus_id'];
   $pat_id=$_GET['pat_id'];
     $pat_name=$_GET['pat_name'];
  //$doc_number = $_SERVER['doc_number'];
    $date=date('Y-m-d');

// Doctor's campus from session (set at login). Still used for some legacy flows
$campus_id = isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : null;

// Pharmacy location selection for doctor (same pattern as nurse/lab working location)
$pharmacy_location_id   = isset($_SESSION['pharmacy_location_id']) ? (int) $_SESSION['pharmacy_location_id'] : 0;
$pharmacy_location_name = isset($_SESSION['pharmacy_location_name']) ? $_SESSION['pharmacy_location_name'] : '';

// Allow doctor to set or change pharmacy location explicitly
if (isset($_POST['set_pharmacy_location'])) {
    $loc = $_POST['pharmacy_location'];
    if (is_numeric($loc)) {
        $id = (int) $loc;
        $stmt = $mysqli->prepare("SELECT name FROM pharmacy_location WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $_SESSION['pharmacy_location_id']   = $id;
                $_SESSION['pharmacy_location_name'] = $row['name'];
                $pharmacy_location_id   = $id;
                $pharmacy_location_name = $row['name'];
            } else {
                // store id even if name missing
                $_SESSION['pharmacy_location_id']   = $id;
                $_SESSION['pharmacy_location_name'] = '';
                $pharmacy_location_id   = $id;
                $pharmacy_location_name = '';
            }
            $stmt->close();
        }
    }

    // redirect back to same patient view to avoid resubmission
    $redir = "his_doc_view_single_patient.php?pat_id=".urlencode($pat_id)."&&pat_name=".urlencode($pat_name);
    header("Location: " . $redir);
    exit;
}

if (isset($_GET['clear_pharmacy_location'])) {
    unset($_SESSION['pharmacy_location_id']);
    unset($_SESSION['pharmacy_location_name']);
    $pharmacy_location_id   = 0;
    $pharmacy_location_name = '';

    $redir = "his_doc_view_single_patient.php?pat_id=".urlencode($pat_id)."&&pat_name=".urlencode($pat_name);
    header("Location: " . $redir);
    exit;
}

// Legacy campus label (may still be used as a fallback in some UIs)
$campus_label = '';
if ($campus_id) {
    $campusNameStmt = $mysqli->prepare("SELECT name FROM campus_locations WHERE id = ? LIMIT 1");
    if ($campusNameStmt) {
        $campusNameStmt->bind_param('i', $campus_id);
        $campusNameStmt->execute();
        $cRes = $campusNameStmt->get_result();
        if ($cRow = $cRes->fetch_assoc()) {
            $campus_label = $cRow['name'];
        }
        $campusNameStmt->close();
    }

    if ($campus_label === '' || $campus_label === null) {
        $campus_label = 'Campus ID ' . $campus_id;
    }
}

            $result=mysqli_query($mysqli,"select * from doc_procedure where date='$date'");
            $reply = mysqli_fetch_array($result);
            $num = mysqli_num_rows($result);
            if($num >  0){
                $amnt=$reply['Total'];
                $bal=$amnt;
           }else{
                $bal='0';
            }


     $cons = 0;
$proc = getPro($mysqli);
$drg  = getPattotdrug($mysqli, $date, $pat_id); // returns sum(amount)
$tot  = $cons + $proc + $drg; // overall total bill           

if(isset($_GET['dels'])){
    $id=$_GET['dels'];
            $query="delete from drug_prescription where id=?";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('s',$id);
            $stmt->execute();
            if($stmt)
            {
                $success = "Drug Deleted Successfully";
                header("location: his_doc_view_single_patient.php?pat_id=$pat_id&&pat_name=$pat_name;");

            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}

 if(isset($_POST['Refer'])){
  $rbox=trim($_POST['slbox']);
  $slab=trim($_POST['slab']);
  $name=$_GET['pat_name'];
  $date=date('Y-m-d');
  $result='';
  $category='';

        
    if(!empty($rbox) && empty($slab)){
            $sql="insert into refer values(0,'$date','$name','$rbox')";
            $stmt=mysqli_query($mysqli,$sql); 

            $query="insert into patient_scan(date,code,name,test,result,category) values(?,?,?,?,?,?)";
                    $st = $mysqli->prepare($query);
                    $rc=$st->bind_param('ssssss',$date,$pat_id,$name,$rbox,$result,$category);
                    $st->execute();

             if($st){
                        $success = "Patient Refered For Test Successfully";
                    }
                    else {
                        $err = "Please Try Again Or Try Later";
                    }
            }
            elseif(empty($rbox) && !empty($slab)){

            $query="insert into patient_lab(date,code,name,test,result,category) values(?,?,?,?,?,?)";
                    $st = $mysqli->prepare($query);
                    $rc=$st->bind_param('ssssss',$date,$pat_id,$name,$slab,$result,$category);
                    $st->execute();

             if($st){
                        $success = "Patient Refered For Test Successfully";
                    }
                    else {
                        $err = "Please Try Again Or Try Later";
                    }
            }

            elseif(!empty($rbox) && !empty($slab)){
                $sql="insert into refer values(0,'$date','$name','$rbox')";
                $stmt=mysqli_query($mysqli,$sql); 

            $query="insert into patient_scan(date,code,name,test,result,category) values(?,?,?,?,?,?)";
                    $st = $mysqli->prepare($query);
                    $rc=$st->bind_param('ssssss',$date,$pat_id,$name,$rbox,$result,$category);
                    $st->execute();

              $query2="insert into patient_lab(date,code,name,test,result,category) values(?,?,?,?,?,?)";
                    $st2 = $mysqli->prepare($query2);
                    $rc2=$st2->bind_param('ssssss',$date,$pat_id,$name,$slab,$result,$category);
                    $st2->execute();      

            if($stmt)
                    {
                        $success = "Patient Refered For Test Successfully";
                    }
                    else {
                        $err = "Please Try Again Or Try Later";
                    }
       } 

        else{$err = "No Action Selected Please Try Again Or Try Later";
        
        }


      }
    

function getcampus($campusid,$mysqli){
    $sql="SELECT * FROM campus_locations where id=$campusid"; 
   $result = mysqli_query($mysqli,$sql);
    $num=mysqli_num_rows($result);
    $reply = mysqli_fetch_array($result);
    $name=$reply['name'];
    return $name;
}

 if (isset($_POST['prdrug'])) {
    $date = date('Y-m-d');
    $drug = trim($_POST['drug']);
    $qnt = trim($_POST['qnt']);
    $const = trim($_POST['const']);
    $dcate = trim($_POST['dcate']);
    $duration = trim($_POST['duration']);

    // Helper functions to calculate hours and duration
    $dur = getdurtn($duration);
    $hly = gethly($const);

    // Total dosage over full duration
    $tot = $qnt * $hly * $dur;

    // Display string for table
    $totdrug = $tot . $dcate;

    // ✅ Insert properly using prepared statement with field names
   $sql = "INSERT INTO drug_prescription 
(date, patid, name, drug, qnt, const, duration, total, totdrug, amount, cate)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}

// Compute total amount (respect doctor's campus if available)
$dtot = getdrugtot($mysqli, $drug, $tot, $campus_id);

// Guarantee a numeric value
if (!isset($dtot) || $dtot === null || $dtot === '' || !is_numeric($dtot)) {
    $dtot = 0;
}

error_log("DEBUG: About to insert drug_prescription with dtot={$dtot}");

$stmt->bind_param('sssssssssss',
    $date, $pat_id, $pat_name, $drug, $qnt, $const, $duration, $tot, $totdrug, $dtot, $dcate
);

// Debug check before executing
error_log("Executing insert with amount = " . $dtot);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

        $success = "Drug processed successfully!";
   // } else {
    //    $err = "Error processing drug. Please try again.";
   // }
}



 if(isset($_POST['finalsub'])){
    $date=date('Y-m-d');
    $visit   = isset($_POST['visit'])   ? trim($_POST['visit'])   : '';
    $diag    = isset($_POST['diagbox']) ? trim($_POST['diagbox']) : '';
    $pro     = isset($_POST['probox'])  ? trim($_POST['probox'])  : '';
    $plan    = isset($_POST['plan'])    ? trim($_POST['plan'])    : '';
    $slbox   = isset($_POST['slbox'])   ? trim($_POST['slbox'])   : '';
    $proamnt = isset($_POST['proamnt']) ? floatval($_POST['proamnt']) : 0;
    $cons    = isset($_POST['cons'])    ? floatval($_POST['cons'])    : 0;
    $totbill=$proamnt + $cons;
    // Use prepared statements for inserts
    $dstmt = $mysqli->prepare("INSERT INTO outpatient_visist_record (date, patid, name, diagnosis, proceedure, plan, doc_incharge) VALUES (?, ?, ?, ?, ?, ?, '')");
    if ($dstmt) {
        $dstmt->bind_param('sissss', $date, $pat_id, $pat_name, $diag, $pro, $plan);
        $dstmt->execute();
        $dstmt->close();
    }

    $pstmt = $mysqli->prepare("INSERT INTO patient_bill (date, patid, name, const, proceedure, total, doc_incharge) VALUES (?, ?, ?, ?, ?, ?, '')");
    if ($pstmt) {
        $pstmt->bind_param('sissds', $date, $pat_id, $pat_name, $cons, $proamnt, $totbill);
        $pstmt->execute();
        $pstmt->close();
    }

     $result=mysqli_query($mysqli,"select * from drug_prescription where date='$date' and patid='$pat_id'");
            while($reply = mysqli_fetch_array($result)){
                $drug=$reply['drug'];
                $qnt=$reply['qnt'];
                $const=$reply['const'];
                $duration=$reply['duration'];
                $total=$reply['total'];
                $totdrug=$reply['totdrug'];
                $amnt=$reply['amount'];
                 $cate=$reply['cate'];
                 $sql="insert into patient_drug_history values(0,'$date','$pat_id','$pat_name','$drug','$qnt','$const','$duration','$total','$totdrug','$amnt','$cate')";
                  $sq=mysqli_query($mysqli,$sql); 

                   // Insert pharmacy order and include campus/location when available
                   // Align with DB schema: pharmacy_order columns per hospital.sql
                   // (`id`, `trackid`, `customer`, `drug`, `Qnt`, `const`, `amount`, `status`, `date`)
                   $order_cols = "trackid, customer, drug, Qnt, const, amount, status, date";
                   $order_values = array($pat_id, $pat_name, $drug, $qnt, $const, $amnt, 'Not Paid', $date);

                   // Check if pharmacy_order has campus_id / pharmacy_location_id columns
                   $order_campus_col = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_order' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
                   $order_loc_col    = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_order' AND COLUMN_NAME='pharmacy_location_id'")->fetch_assoc()['cnt'] ?? 0;

                   if ($order_campus_col && $campus_id) {
                       $order_cols .= ", campus_id";
                       $order_params .= ",?";
                       $order_values[] = $campus_id;
                   }

                   $pharmacy_location_id_for_order = isset($_SESSION['pharmacy_location_id']) ? (int) $_SESSION['pharmacy_location_id'] : null;
                   if ($order_loc_col && $pharmacy_location_id_for_order) {
                       $order_cols .= ", pharmacy_location_id";
                       $order_params .= ",?";
                       $order_values[] = $pharmacy_location_id_for_order;
                   }

                   // Build prepared statement dynamically and bind robustly
                   $placeholders = implode(',', array_fill(0, count($order_values), '?'));
                   $ins_sql = "INSERT INTO pharmacy_order ($order_cols";
                   // append optional campus/location columns
                   if ($order_campus_col && $campus_id) {
                       $ins_sql .= ", campus_id";
                   }
                   if ($order_loc_col && $pharmacy_location_id_for_order) {
                       $ins_sql .= ", pharmacy_location_id";
                   }
                   $ins_sql .= ") VALUES ($placeholders";
                   if ($order_campus_col && $campus_id) { $ins_sql .= ',?'; }
                   if ($order_loc_col && $pharmacy_location_id_for_order) { $ins_sql .= ',?'; }
                   $ins_sql .= ')';

                   $ins_stmt = $mysqli->prepare($ins_sql);
                   if ($ins_stmt) {
                       // build types string and references for bind_param
                       $types = '';
                       $refs = array();
                       foreach ($order_values as $k => $v) {
                           $types .= is_int($v) ? 'i' : 's';
                           $refs[$k] = &$order_values[$k];
                       }
                       if ($order_campus_col && $campus_id) {
                           $types .= 'i';
                           $order_values[] = $campus_id;
                           $refs[] = &$order_values[count($order_values)-1];
                       }
                       if ($order_loc_col && $pharmacy_location_id_for_order) {
                           $types .= 'i';
                           $order_values[] = $pharmacy_location_id_for_order;
                           $refs[] = &$order_values[count($order_values)-1];
                       }
                       array_unshift($refs, $types);
                       call_user_func_array(array($ins_stmt, 'bind_param'), $refs);
                       $ins_stmt->execute();
                   } else {
                       // fallback: explicit column list (safer than relying on table order)
                       $fallback_cols = "(trackid, customer, drug, Qnt, const, amount, status, date";
                       if ($order_campus_col && $campus_id) { $fallback_cols .= ", campus_id"; }
                       if ($order_loc_col && $pharmacy_location_id_for_order) { $fallback_cols .= ", pharmacy_location_id"; }
                       $fallback_cols .= ')';
                       $sqll = "INSERT INTO pharmacy_order $fallback_cols VALUES (0,'$pat_id','$pat_name','$drug','$qnt','$const','$amnt','Not Paid','$date'";
                       if ($order_campus_col && $campus_id) { $sqll .= "," . intval($campus_id); }
                       if ($order_loc_col && $pharmacy_location_id_for_order) { $sqll .= "," . intval($pharmacy_location_id_for_order); }
                       $sqll .= ')';
                       $sqs = mysqli_query($mysqli,$sqll);
                   }

                  $sdel="delete from drug_prescription where date='$date' and patid='$pat_id'";
                  $dq=mysqli_query($mysqli,$sdel);

                  $sdiag="delete from doc_diagnosis";
                  $sdiaq=mysqli_query($mysqli,$sdiag);

                  $spro="delete from doc_procedure";
                  $spq=mysqli_query($mysqli,$spro);  
            }
            //if($spq){
                $success="Doctor Has Succesfully Attended To You !!! ";
            //}else {
                //$err="Error In Posting ! Please Try Again";
           // }

}

function getdrugtot($mysqli, $dname, $qn, $campus_id = null) {
    $bal = 0;
    $amnt = 0; // default value

    // Doctor-selected pharmacy location (if any) takes precedence
    $pharmacy_location_id = isset($_SESSION['pharmacy_location_id']) ? (int) $_SESSION['pharmacy_location_id'] : null;

    // If pharmacy table exists with campus_id, prefer campus-specific amount
    $pharm_table_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy'")->fetch_assoc()['cnt'] ?? 0;
    $pharm_col_exists = 0;
    $pharm_loc_col_exists = 0;
    if ($pharm_table_exists) {
        $pharm_col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
        $pharm_loc_col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy' AND COLUMN_NAME='pharmacy_location_id'")->fetch_assoc()['cnt'] ?? 0;
    }

    // First preference: pharmacy_location_id if available and selected
    if ($pharm_table_exists && $pharm_loc_col_exists && $pharmacy_location_id) {
        $stmt = $mysqli->prepare("SELECT amount FROM pharmacy WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND pharmacy_location_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('si', $dname, $pharmacy_location_id);
            $stmt->execute();
            $stmt->bind_result($amnt);
            $stmt->fetch();
            $stmt->close();
        }
    }

    // Second preference: campus_id column (legacy multi-campus model)
    if (($amnt === 0 || $amnt === null || $amnt === '') && $pharm_table_exists && $pharm_col_exists && $campus_id) {
        $stmt = $mysqli->prepare("SELECT amount FROM pharmacy WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND campus_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('si', $dname, $campus_id);
            $stmt->execute();
            $stmt->bind_result($amnt);
            $stmt->fetch();
            $stmt->close();
        }
    }

    // Fallback within pharmacy table without any location filter
    if (($amnt === 0 || $amnt === null || $amnt === '') && $pharm_table_exists) {
        $stmt = $mysqli->prepare("SELECT amount FROM pharmacy WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $dname);
            $stmt->execute();
            $stmt->bind_result($amnt);
            $stmt->fetch();
            $stmt->close();
        }
    } else {
        // Fallback: attempt to find price in `drug` table if present
        $drug_table_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='drug'")->fetch_assoc()['cnt'] ?? 0;
        if ($drug_table_exists) {
            $stmt = $mysqli->prepare("SELECT amount FROM drug WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $dname);
                $stmt->execute();
                $stmt->bind_result($amnt);
                $stmt->fetch();
                $stmt->close();
            }
        }
    }

    // If no matching drug found or NULL amount, default to 0
    if ($amnt === null || $amnt === '') {
        $amnt = 0;
    }

    $bal = floatval($amnt) * floatval($qn);
    return $bal;
}

function getPattotdrug($mysqli,$date,$pid){
            $bal=0;
            $result=mysqli_query($mysqli,"select * from drug_prescription where date='$date' and patid='$pid'");
            while($reply = mysqli_fetch_array($result)){
                $amnt=$reply['amount'];
                $bal+=$amnt;
            }
            return $bal;
        }

 function getPro($mysqli){
        $date=date('Y-m-d');
            $bal=0;
            $result=mysqli_query($mysqli,"select * from doc_procedure where date='$date'");
            $reply = mysqli_fetch_array($result);
            $num = mysqli_num_rows($result);
             if($num >  0){
                $amnt=$reply['Total'];
                $bal=$amnt;

                return $bal;
           }else{
               return $bal=0;
            }

        }
function getdurtn($val){
    if($val=='7/7'){
        return 7;
    }else if($val=='6/7'){
        return 6;
    }else if($val=='5/7'){
        return 5;
    }else if($val=='4/7'){
        return 4;
    }else if($val=='3/7'){
        return 3;
    }else if($val=='2/7'){
        return 2;
    }else if($val=='1/7'){
        return 1;
    }else{
        return 0;
    }
}
  function gethly($val){
    if($val=='24hly'){
        return 1;
    }else if($val=='12hly'){
        return 2;
    }else if($val=='8hly'){
        return 3;
    }else if($val=='6hly'){
        return 4;
    }else if($val=='4hly'){
        return 6;
    }else if($val=='3hly'){
        return 8;
    }else if($val=='2hly'){
        return 10;
    }else{
        return 0;
    }
  }
?>

<!DOCTYPE html>
    <html lang="en">

    <?php include('assets/inc/head.php');?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
             <?php include("assets/inc/nav.php");?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
                <?php include("assets/inc/sidebar.php");?>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <!--Get Details Of A Single User And Display Them Here-->

            <?php
                $pat_id=$_GET['pat_id'];
                 $pat_name=$_GET['pat_name'];
                 $rt="SELECT * FROM sendsignal where pat_code='$pat_id'"; 
                $stt= $mysqli->prepare($rt) ;
                $stt->execute() ;//ok
                $rs=$stt->get_result();
                $rw=$rs->fetch_object();
                $time=$rw->Time;
                 
                /*$ret="SELECT  * FROM his_patients WHERE pat_id=?";
                $stmt= $mysqli->prepare($ret);
                $stmt->bind_param('i',$pat_id);
                $stmt->execute() ;//ok
                $res=$stmt->get_result();
                //$cnt=1;
                while($row=$res->fetch_object())
            {
                $mysqlDateTime = $row->pat_date_joined;*/

                $surn = $firstname = $mname = $phone = $cate = $nok = $add = $nokph = $dob = $date = $pic = "";

// Identify patient type
if (strpos($pat_id, 'IND') !== false) {
    $ret = "SELECT * FROM individual WHERE code = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'F') !== false) {
    $ret = "SELECT * FROM family_individual WHERE code = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'ST') !== false) {
    $ret = "SELECT * FROM student WHERE STcode = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'S') !== false) {
    $ret = "SELECT * FROM staff WHERE Scode = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'A') !== false) {   
    $ret = "SELECT * FROM antenatal WHERE Acode = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'A') !== false) {
    $ret = "SELECT * FROM individual WHERE code = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
else {
    $stmt = null;
}

// Fetch if statement was prepared
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_object()) {
        $surn = $row->surname;
        $firstname = $row->firstname;
        $mname = $row->middlename;
        $phone = $row->phone;
        $add = $row->address ?? '';
        $nok = $row->nok ?? '';
        $nokph = $row->nok_phone ?? $row->nok_contact ?? '';
        $dob = $row->dob;
        $date = $row->reg_date;
        $pic = $row->picture;
        $cate = $cate ?: "PATIENT CARD";
    }
}
?>


            ?>
            <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Patients</a></li>
                                            <li class="breadcrumb-item active">View Patients</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title"><?php echo $surn." ".$firstname." ".$mname;?>'s Profile</h4>
                                    <h2><?php echo getcampus($campusid,$mysqli); ?></h2>
                               
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-4 col-xl-4">
                                <div class="card-box text-center">
                                    <img src="../picture/<?php echo $pic; ?>" width="170" height="150" 
                                        alt="profile-image">
                                    
                                    <div class="text-left mt-3">
                                        
                                        <p class="text-muted mb-2 font-13"><strong>Patient Number :</strong> <span class="ml-2"><?php echo $pat_id;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Full Name :</strong> <span class="ml-2"><?php echo $surn." ".$firstname." ".$mname;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Mobile :</strong><span class="ml-2"><?php echo $phone;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Card Type :</strong><span class="ml-2"><?php echo $cate;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Address :</strong> <span class="ml-2"><?php echo $add;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Date Of Birth :</strong> <span class="ml-2"><?php echo $dob;?></span></p>
                                        <div>
                                           <?php 
                                                if (!empty($dob)) {
                                                    try {
                                                        $dobDate = new DateTime($dob);
                                                        $today = new DateTime(); 
                                                        $age = $today->diff($dobDate)->y; 
                                                        echo '<p class="text-muted mb-2 font-13"><strong>Age :</strong> <span class="ml-2">' . $age . ' years</span></p>';
                                                    } catch (Exception $e) {
                                                        echo '<p class="text-danger">Invalid Date Format</p>';
                                                    }
                                                } else {
                                                    echo '<p class="text-muted mb-2 font-13"><strong>Age :</strong> <span class="ml-2">N/A</span></p>';
                                                }
                                            ?>
                                        </div>
                                        
                                        <p class="text-muted mb-2 font-13"><strong>Date Registered :</strong> <span class="ml-2"><?php echo date("d/m/Y - h:m", strtotime($date));?></span></p>
                                        <p class="text-muted mb-2 font-13"> <span class="ml-2"><a href="<?php  ?>"><strong><h3>Patient Medical History</h3></strong></a></span></p>
                                        <hr>




                                    </div>

                                </div> <!-- end card-box -->

                            </div> <!-- end col-->
                            
                           
                            <div class="col-lg-8 col-xl-8">
                                <div class="card-box">
                                    <ul class="nav nav-pills navtab-bg nav-justified">
                                         <li class="nav-item">
                                            <a href="#aboutdc" data-toggle="tab" aria-expanded="false" class="nav-link">
                                                Doctor's Portal
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#aboutme" data-toggle="tab" aria-expanded="true" class="nav-link">
                                               Scan/X-Ray Result
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#timeline" data-toggle="tab" aria-expanded="true" class="nav-link active">
                                                 Vitals
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#settings" data-toggle="tab" aria-expanded="true" class="nav-link">
                                                Laboratory
                                            </a>
                                        </li>

                                    </ul>
                                    <!--Medical History-->

                                    <form method="post" enctype="multipart/form-data">
                                    <div class="tab-content">
                                        <div class="tab-pane show " id="aboutdc">
                                            <div class="col-md-12">
                                                             <div class="form-group">
                                                              <label for="comment">Plan</label>
                                                              <textarea class="form-control" id="comment" name="plan" rows="3">
                                                              </textarea>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                             <div class="form-group">
                                                              <label for="comment">Observation</label>
                                                              <textarea class="form-control" id="comment" name="observation" rows="3">
                                                              </textarea>
                                                            </div>
                                                        </div>
                                            <div class="table-responsive">
                                                <table class="table table-borderless mb-0">
                                                
                                                       <div class="col-md-12">
                                                             <div class="form-group">
                                                              <label for="comment">Diagnosis</label>
                                                              <textarea class="form-control" id="comment" name="diagnosis" rows="3">
                                                              </textarea>
                                                            </div>
                                                            </div>
                                                        <div class="col-md-12">
                                                             <div class="form-group">
                                                              <label for="comment">Procedures</label>
                                                              <textarea class="form-control" id="comment" name="procedures" rows="3">
                                                                 </textarea>
                                                            </div>
                                                        </div> <!-- end col -->


                                                    </div> <!-- end row -->
                                                

                                                    <div class="row">
                                        
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="firstname">Refer For Scan Test</label>
                                                                   <select id="inputState" onChange="getslsubmit(this.value);" required="required" name="scan" class="form-control">
                                                            <option>Choose</option>
                                                        <?php
                                                            $sql = "SELECT * FROM scan order by id ASC";
                                                            $result = mysqli_query($mysqli,$sql);
                                                            while($reply = mysqli_fetch_array($result)){
                                                                echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";
                                                            }
                                                        ?>
                                                     
                                                    </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="lastname">Refer for Laboratory Test</label>
                                                               <select id="l" onChange="getslab(this.value);" required="required" name="lab"  class="form-control">
                                                        <option>Choose</option>
                                                         
                                                        <?php
                                                            $sql = "SELECT * FROM lab order by id ASC";
                                                            $result = mysqli_query($mysqli,$sql);
                                                            while($reply = mysqli_fetch_array($result)){
                                                                echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div>
                                                <div class="row">
                                               
                                                    <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="firstname">Scan Test</label>
                                                                <textarea class="form-control" id="slname" name="slbox" rows="2">
                                                              </textarea>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="firstname">Laboratory Test</label>
                                                                <textarea class="form-control" id="slab" name="slab" rows="2">
                                                              </textarea>
                                                            </div>
                                                        </div>

                                                         <div class="col-md-3">
                                                             <div class="text-left">
                                                            <button type="submit" name="Refer" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i>Refer Patient for Test</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                             <div class="text-left">
                                                            <button type="button"  class="btn btn-danger waves-effect waves-light mt-2" onclick="getdelete();" ><i class="mdi mdi-content-save"></i>Clear Diagnosis</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                             <div class="text-left">
                                                            <button type="button"  class="btn btn-danger waves-effect waves-light mt-2" onclick="getsdelete();" ><i class="mdi mdi-content-save"></i>Clear Scan & Laboratory</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                             <div class="text-left">
                                                            <button type="submit" name="prdrug" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i>Process Patient Drug</button>
                                                            </div>
                                                        </div>

                                                    </div> <!-- end row -->

                                                    <div class="row">
                                                    <!-- Pharmacy location selector inline with prescription fields -->
                                                    <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="pharmacy_location_select">Pharmacy Location</label>
                                                                <?php if (!empty($pharmacy_location_name)): ?>
                                                                    <small class="form-text text-muted">Current: <?php echo htmlspecialchars($pharmacy_location_name); ?></small>
                                                                <?php endif; ?>
                                                                <select id="pharmacy_location_select" name="pharmacy_location" class="form-control">
                                                                    <option value="">-- Select --</option>
                                                                    <?php
                                                                    $pl_res = $mysqli->query("SELECT id, name FROM pharmacy_location ORDER BY name ASC");
                                                                    if ($pl_res) {
                                                                        while ($pl = $pl_res->fetch_assoc()) {
                                                                            $selected = (!empty($pharmacy_location_id) && (int)$pharmacy_location_id === (int)$pl['id']) ? 'selected' : '';
                                                                            echo "<option value='".intval($pl['id'])."' " . $selected . ">".htmlspecialchars($pl['name'])."</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                                <button type="submit" name="set_pharmacy_location" formnovalidate class="btn btn-sm btn-primary mt-1">Set</button>
                                                            </div>
                                                        </div>

                                                    <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="lastname">List of Drugs</label>
                                                                <?php if (!empty($pharmacy_location_name)): ?>
                                                                    <small class="form-text text-muted">Listing drugs from <?php echo htmlspecialchars($pharmacy_location_name); ?> pharmacy</small>
                                                                <?php elseif (!empty($campus_label)): ?>
                                                                    <small class="form-text text-muted">Listing drugs from <?php echo htmlspecialchars($campus_label); ?> pharmacy</small>
                                                                <?php endif; ?>
                                                               <select id="name" name="drug"  class="form-control">
                                                            <option>Choose</option>
                                                         
                                                                <?php
                                                                    /* Prefer listing drugs available in the doctor's campus when the `pharmacy` table exists.
                                                                       Fallback to `pharmacy` without campus filter or `drug` table if pharmacy is not available. */
                                                                    $pharm_table_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy'")->fetch_assoc()['cnt'] ?? 0;
                                                                    $pharm_col_exists = 0;
                                                                    $pharm_loc_col_exists = 0;
                                                                    if ($pharm_table_exists) {
                                                                        $pharm_col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
                                                                        $pharm_loc_col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy' AND COLUMN_NAME='pharmacy_location_id'")->fetch_assoc()['cnt'] ?? 0;
                                                                    }

                                                                    if ($pharm_table_exists && $pharm_loc_col_exists && !empty($pharmacy_location_id)) {
                                                                        $ps = $mysqli->prepare("SELECT DISTINCT name FROM pharmacy WHERE pharmacy_location_id = ? ORDER BY name ASC");
                                                                        $ps->bind_param('i', $pharmacy_location_id);
                                                                        $ps->execute();
                                                                        $pres = $ps->get_result();
                                                                        while ($reply = $pres->fetch_assoc()) {
                                                                            echo "<option value=\"".htmlspecialchars($reply['name'])."\">".htmlspecialchars($reply['name'])."</option>";
                                                                        }
                                                                    } elseif ($pharm_table_exists && $pharm_col_exists && $campus_id) {
                                                                        $ps = $mysqli->prepare("SELECT DISTINCT name FROM pharmacy WHERE campus_id = ? ORDER BY name ASC");
                                                                        $ps->bind_param('i', $campus_id);
                                                                        $ps->execute();
                                                                        $pres = $ps->get_result();
                                                                        while ($reply = $pres->fetch_assoc()) {
                                                                            echo "<option value=\"".htmlspecialchars($reply['name'])."\">".htmlspecialchars($reply['name'])."</option>";
                                                                        }
                                                                    } elseif ($pharm_table_exists) {
                                                                        $ps = $mysqli->prepare("SELECT DISTINCT name FROM pharmacy ORDER BY name ASC");
                                                                        $ps->execute();
                                                                        $pres = $ps->get_result();
                                                                        while ($reply = $pres->fetch_assoc()) {
                                                                            echo "<option value=\"".htmlspecialchars($reply['name'])."\">".htmlspecialchars($reply['name'])."</option>";
                                                                        }
                                                                    } else {
                                                                        $sql = "SELECT * FROM drug order by id ASC";
                                                                        $result = mysqli_query($mysqli,$sql);
                                                                        while($reply = mysqli_fetch_array($result)){
                                                                            echo "<option value=\"".htmlspecialchars($reply['name'])."\">".htmlspecialchars($reply['name'])."</option>";
                                                                        }
                                                                    }
                                                                ?>
                                                                </select>
                                                                                                                        </div>
                                                                                                                </div> <!-- end col -->

                                                                                                            <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="dosage">Dosage</label>
                                                                <input type="text" name="qnt"  class="form-control" id="firstname" placeholder="Qty">
                                                            </div>
                                                    </div> 

                                                      <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="firstname">Const</label>
                                                                <input type="text" name="const"  class="form-control" id="firstname" placeholder="Hly">
                                                            </div>
                                                    </div> 
                                                    <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="firstname">Category</label>
                                                                   <select id="inputState" required="required" name="dcate"  class="form-control">
                                                             <option>Choose</option>
                                                             <option value="Tab">Tab</option>
                                                            <option value="Cream">Cream</option>
                                                            <option value="Drop">Drop</option>
                                                            <option value="Injection">Injection</option>
                                                     
                                                                </select>
                                                            </div>
                                                        </div> 

                                                    <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="firstname">Duration</label>
                                                                <input type="text" name="duration"  class="form-control" id="firstname" placeholder="Duration">
                                                            </div>
                                                    </div> 
                                                    </form>
                                            <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                                <thead>
                                            <tr>
                                                <th data-hide="phone" style="color:white;">Date</th>
                                                <th data-hide="phone" style="color:white;">Drug</th>
                                                <th data-hide="phone" style="color:white;">Dosage</th>
                                                <th data-hide="phone" style="color:white;">Const</th>
                                                <th data-hide="phone" style="color:white;">Duration</th>
                                                 <th data-hide="phone" style="color:white;">Total</th>
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */  
                                                $date=date('Y-m-d');
                                                $ret="SELECT * FROM drug_prescription where date='$date' ORDER BY id ASC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>

                                                <tr>
                                                    <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->drug;?></td>

                                                    <td><?php echo $row->qnt;?></td>
                                                    <td><?php echo $row->const;?></td>
                                                     <td><?php echo $row->duration;?></td>
                                                    <td><?php echo $row->totdrug; ?></td>
                                                    
                                                    <td><a href="his_doc_view_single_patient.php?dels=<?php echo $row->id;?>"><img src="assets/img/remove.png" height="20" width="20"></a></td>
                                                </tr>
                                                </tbody>
                                            <?php  $cnt = $cnt +1 ; }?>
                                            <tfoot>
                                            <tr class="active">
                                                <td colspan="8">
                                                    <div class="text-right">
                                                        <ul class="pagination pagination-rounded justify-content-end footable-pagination m-t-10 mb-0"></ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tfoot>

                                                    </div>

    
                                                </table>

                                                 <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Consultation</label>
                                                                <input type="text" value="0" style="background-color:darkgrey; color:red;" name="cons" class="form-control" id="firstname" placeholder="">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Drug Bill</label>
                                                                <input type="text" value="<?php echo getPattotdrug($mysqli,date('Y-m-d'),$pat_id); ?>" style="background-color:darkgrey; color:red; font-size:+45;" name="drugamnt" class="form-control" id="firstname" placeholder="">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Procedure</label>
                                                                <input type="text" value="<?php echo getPro($mysqli); ?>" style="background-color:darkgrey; color:red; font-size:+45;" name="proamnt" class="form-control" id="pro" placeholder="">
                                                            </div>
                                                        </div>

                                                         <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Total Bill</label>
                                                                <input type="text" style="background-color:darkgrey; color:red;" name="tbill" value="<?php echo $cons + $proc + getPattotdrug($mysqli, date('Y-m-d'), $pat_id);?>" class="form-control" id="firstname" placeholder="">
                                                            </div>
                                                        </div>

                                                         <div class="col-md-5">
                                                             <div class="text-left">
                                                            <button type="submit" name="finalsub" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i>Save Record</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                            </div>
                                        </div>
                                        </form>
                                        <!-- end vitals content-->



                                        <div class="tab-pane show active" id="aboutme">
                                             <ul class="list-unstyled timeline-sm">
                                                <?php
                                                    $pres_pat_number =$pat_id;
                                                    $ret="SELECT  * FROM patient_scan where code='$pres_pat_number' and result !='' order by id DESC";
                                                    $stmt= $mysqli->prepare($ret) ;
                                                    // $stmt->bind_param('i',$pres_pat_number );
                                                    $stmt->execute() ;//ok
                                                    $res=$stmt->get_result();
                                                    //$cnt=1;
                                                    
                                                    while($row=$res->fetch_object())
                                                        {
                                                    $mysqlDateTime = $row->date; //trim timestamp to date

                                                ?>
                                                    <li class="timeline-sm-item">
                                                        <span class="timeline-sm-date"><?php echo date("Y-m-d", strtotime($mysqlDateTime));?></span>
                                                        <h5 class="mt-0 mb-1"><?php echo $row->test;?></h5>
                                                        <p class="text-muted mt-2">
                                                            <?php echo $row->result;?>
                                                        </p>

                                                    </li>
                                                <?php }?>
                                            </ul>
                                           
                                        </div> <!-- end tab-pane -->
                                        <!-- end Prescription section content -->

                                        <div class="tab-pane show " id="timeline">
                                            <div class="table-responsive">
                                                <table class="table table-borderless mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Body Temperature</th>
                                                            <th>Heart Rate/Pulse</th>
                                                            <th>Respiratory Rate</th>
                                                            <th>Blood Pressure</th>
                                                            <th>Date Recorded</th>
                                                        </tr>
                                                    </thead>
                                                    <?php
                                                        $vit_pat_number =$pat_id;
                                                        $ret="SELECT  * FROM his_vitals WHERE vit_pat_number = '$vit_pat_number' order by id DESC";
                                                        $stmt= $mysqli->prepare($ret) ;
                                                        // $stmt->bind_param('i',$vit_pat_number );
                                                        $stmt->execute() ;//ok
                                                        $res=$stmt->get_result();
                                                        //$cnt=1;
                                                        
                                                        while($row=$res->fetch_object())
                                                            {
                                                        $mysqlDateTime = $row->vit_daterec; //trim timestamp to date

                                                    ?>
                                                        <tbody>
                                                            <tr>
                                                                <td><?php echo $row->vit_bodytemp;?></td>
                                                                <td><?php echo $row->vit_heartpulse;?></td>
                                                                <td><?php echo $row->vit_resprate;?></td>
                                                                <td><?php echo $row->vit_bloodpress;?></td>
                                                                <td><?php echo date("Y-m-d", strtotime($mysqlDateTime));?></td>
                                                            </tr>
                                                        </tbody>
                                                    <?php }?>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- end vitals content-->

                                        <div class="tab-pane" id="settings">
                                            <ul class="list-unstyled timeline-sm">
                                                <?php
                                                    $pres_pat_number =$pat_id;
                                                    $ret="SELECT  * FROM patient_lab where code='$pres_pat_number' and result !='' order by id DESC";
                                                    $stmt= $mysqli->prepare($ret) ;
                                                    // $stmt->bind_param('i',$pres_pat_number );
                                                    $stmt->execute() ;//ok
                                                    $res=$stmt->get_result();
                                                    //$cnt=1;
                                                    
                                                    while($row=$res->fetch_object())
                                                        {
                                                    $mysqlDateTime = $row->date; //trim timestamp to date

                                                ?>
                                                    <li class="timeline-sm-item">
                                                        <span class="timeline-sm-date"><?php echo date("Y-m-d", strtotime($mysqlDateTime));?></span>
                                                        <h5 class="mt-0 mb-1"><?php echo $row->test;?></h5>
                                                        <p class="text-muted mt-2">
                                                            <?php echo $row->result;?>
                                                        </p>

                                                    </li>
                                                <?php }?>

                                            </ul>
                                        </div>
                                        </div>
                                        <!-- end lab records content-->

                                    </div> <!-- end tab-content -->
                                </div> <!-- end card-box-->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                    </div> <!-- container -->

                </div> <!-- content -->

                <!-- Footer Start -->
                <?php include('assets/inc/footer.php');?>
                <!-- end Footer -->

            </div>
            

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- Vendor js -->
        <script src="assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="assets/js/app.min.js"></script>

    </body>

<script type="text/javascript">

        function getsubmit(val) {
    $.ajax({
    type: "POST",
    url: "get_dsubmit.php",
    data:'name='+val,
    success: function(data){
        $("#name").html(data);
    }
    });
}


function getpsubmit(val) {
    $.ajax({
    type: "POST",
    url: "get_psubmit.php",
    data:'name='+val,
    success: function(data){
        $("#pname").html(data);
    }
    });
    getproceed(val)
}
function getproceed(val) {
    $.ajax({
    type: "POST",
    url: "get_proceed.php",
    data:'name='+val,
    success: function(data){
        $("#pro").html(data);
    }
    });
}

function getslsubmit(val) {
    $.ajax({
    type: "POST",
    url: "get_slsubmit.php",
    data:'name='+val,
    success: function(data){
        $("#slname").html(data);
    }
    });
}

function getslab(val) {
    $.ajax({
    type: "POST",
    url: "get_labsubmit.php",
    data:'name='+val,
    success: function(data){
        $("#slab").html(data);
    }
    });
}

function getdelete(val) {
    $.ajax({
    type: "POST",
    url: "get_delete.php",
    data:'name='+val,
    success: function(data){
        $("#name").html(data);
        $("#pname").html(data);
    }
    });
}

function getsdelete(val) {
    $.ajax({
    type: "POST",
    url: "get_sdelete.php",
    data:'name='+val,
    success: function(data){
        $("#slname").html(data);
         $("#slab").html(data);
    }
    });
}

    </script>
</html>