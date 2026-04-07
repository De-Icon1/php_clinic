<?php
    // Server side: handle Patient Registration
    // Start output buffering early to avoid "headers already sent" issues on some servers
    if (!ob_get_level()) ob_start();
    if (session_status() == PHP_SESSION_NONE) session_start();
    include('assets/inc/config.php');
    $ind=isset($_GET['id']) ? $_GET['id'] : null;

// Helper: fetch one row from a prepared statement in a mysqlnd-safe way.
function fetch_one_stmt($stmt){
    if(method_exists($stmt, 'get_result')){
        $res = $stmt->get_result();
        return $res ? $res->fetch_object() : null;
    }
    $meta = $stmt->result_metadata();
    if(!$meta) return null;
    $fields = array();
    $row = array();
    $bind = array();
    while($f = $meta->fetch_field()){
        $fields[] = $f->name;
        $bind[] = & $row[$f->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $bind);
    if($stmt->fetch()){
        $obj = new stdClass();
        foreach($row as $k=>$v) $obj->$k = $v;
        return $obj;
    }
    return null;
}

     if (strpos($ind, 'IND') !== false) {

    $ret="SELECT surname,firstname,middlename,phone,nok,nok_contact AS nok_contact,dob,reg_date,picture FROM individual where code=?"; 
    $stmt= $mysqli->prepare($ret) ;
    if($stmt){
        $stmt->bind_param('s', $ind);
        $stmt->execute();
        $row = fetch_one_stmt($stmt);
        if($row){
            $surn=$row->surname;
            $firstname=$row->firstname;
            $mname=$row->middlename;
            $phone=$row->phone;
            $cate="INDIVIDUAL CARD";
            $nok=isset($row->nok) ? $row->nok : '';
            $nokph=isset($row->nok_contact) ? $row->nok_contact : '';
            $dob=isset($row->dob) ? $row->dob : '';
            $date=isset($row->reg_date) ? $row->reg_date : '';
            $pic=isset($row->picture) ? $row->picture : '';
        } else {
            $surn=''; $firstname=''; $mname=''; $phone=''; $cate=''; $nok=''; $nokph=''; $dob=''; $date=''; $pic='';
        }
        $stmt->close();
    }
}
elseif (strpos($ind, 'F') !== false) {
    $ret="SELECT surname,firstname,middlename,phone,nok,nok_contact AS nok_contact,dob,reg_date,picture FROM family_individual where code=?"; 
    $stmt= $mysqli->prepare($ret) ;
    if($stmt){
        $stmt->bind_param('s', $ind);
        $stmt->execute();
        $row = fetch_one_stmt($stmt);
        if($row){
            $surn=$row->surname;
            $firstname=$row->firstname;
            $mname=$row->middlename;
            $cate="FAMILY CARD";
            $phone=$row->phone;
            $nok=isset($row->nok) ? $row->nok : '';
            $nokph=isset($row->nok_contact) ? $row->nok_contact : '';
            $dob=isset($row->dob) ? $row->dob : '';
            $date=isset($row->reg_date) ? $row->reg_date : '';
            $pic=isset($row->picture) ? $row->picture : '';
        } else {
            $surn=''; $firstname=''; $mname=''; $phone=''; $cate=''; $nok=''; $nokph=''; $dob=''; $date=''; $pic='';
        }
        $stmt->close();
    }
}
elseif (strpos($ind, 'ST') !== false) {
    $ret="SELECT surname,firstname,middlename,phone,nok,nok_contact AS nok_contact,dob,reg_date,picture,matric_no FROM student where STcode=?"; 
    $stmt= $mysqli->prepare($ret) ;
    if($stmt){
        $stmt->bind_param('s', $ind);
        $stmt->execute();
        $row = fetch_one_stmt($stmt);
        if($row){
            $surn=$row->surname;
            $firstname=$row->firstname;
            $mname=$row->middlename;
            $cate="STUDENT CARD";
            $phone=$row->phone;
            $nok=isset($row->nok) ? $row->nok : '';
            $nokph=isset($row->nok_contact) ? $row->nok_contact : '';
            $dob=isset($row->dob) ? $row->dob : '';
            $date=isset($row->reg_date) ? $row->reg_date : '';
            $pic=isset($row->picture) ? $row->picture : '';
        } else {
            $surn=''; $firstname=''; $mname=''; $phone=''; $cate=''; $nok=''; $nokph=''; $dob=''; $date=''; $pic='';
        }
        $stmt->close();
    }
}
elseif (strpos($ind, 'S') !== false) {
    $ret="SELECT surname,firstname,middlename,phone,nok,nok_contact AS nok_contact,dob,reg_date,picture,staff_no FROM staff where Scode=?"; 
    $stmt= $mysqli->prepare($ret) ;
    if($stmt){
        $stmt->bind_param('s', $ind);
        $stmt->execute();
        $row = fetch_one_stmt($stmt);
        if($row){
            $surn=$row->surname;
            $firstname=$row->firstname;
            $cate="STAFF CARD";
            $mname=$row->middlename;
            $phone=$row->phone;
            $nok=isset($row->nok) ? $row->nok : '';
            $nokph=isset($row->nok_contact) ? $row->nok_contact : '';
            $dob=isset($row->dob) ? $row->dob : '';
            $date=isset($row->reg_date) ? $row->reg_date : '';
            $pic=isset($row->picture) ? $row->picture : '';
        } else {
            $surn=''; $firstname=''; $mname=''; $phone=''; $cate=''; $nok=''; $nokph=''; $dob=''; $date=''; $pic='';
        }
        $stmt->close();
    }
}
elseif (strpos($ind, 'H') !== false) {
    $ret="SELECT surname,firstname,Lastname AS middlename,phone,nok,nok_contact AS nok_contact, dob,reg_date,picture FROM hmocompany_individual where code=?"; 
    $stmt= $mysqli->prepare($ret) ;
    if($stmt){
        $stmt->bind_param('s', $ind);
        $stmt->execute();
        $row = fetch_one_stmt($stmt);
        if($row){
            $surn=$row->surname;
            $firstname=$row->firstname;
            $mname=isset($row->middlename) ? $row->middlename : '';
            $phone=$row->phone;
            $cate="HMO CARD";
            $nok=isset($row->nok) ? $row->nok : '';
            $nokph=isset($row->nok_phone) ? $row->nok_phone : '';
            $dob=isset($row->dob) ? $row->dob : '';
            $date=isset($row->reg_date) ? $row->reg_date : '';
            $pic=isset($row->picture) ? $row->picture : '';
        } else {
            $surn=''; $firstname=''; $mname=''; $phone=''; $cate=''; $nok=''; $nokph=''; $dob=''; $date=''; $pic='';
        }
        $stmt->close();
    }
}
elseif (strpos($ind, 'A') !== false) {
    $ret="SELECT surname,firstname,middlename,phone,nok,nok_contact AS nok_contact,dob,reg_date,picture FROM antenatal where acode=?"; 
    $stmt= $mysqli->prepare($ret) ;
    if($stmt){
        $stmt->bind_param('s', $ind);
        $stmt->execute();
        $row = fetch_one_stmt($stmt);
        if($row){
            $surn=$row->surname;
            $firstname=$row->firstname;
            $mname=$row->middlename;
            $phone=$row->phone;
            $cate="ANTENATAL CARD";
            $nok=isset($row->nok) ? $row->nok : '';
            $nokph=isset($row->nok_phone) ? $row->nok_phone : '';
            $dob=isset($row->dob) ? $row->dob : '';
            $date=isset($row->reg_date) ? $row->reg_date : '';
            $pic=isset($row->picture) ? $row->picture : '';
        } else {
            $surn=''; $firstname=''; $mname=''; $phone=''; $cate=''; $nok=''; $nokph=''; $dob=''; $date=''; $pic='';
        }
        $stmt->close();
    }
}
elseif (strpos($ind, 'A') !== false) {
    $ret="SELECT surname,firstname,middlename,phone,nok,nok_contact AS nok_contact,dob,reg_date,picture FROM individual where code=?"; 
    $stmt= $mysqli->prepare($ret) ;
    if($stmt){
        $stmt->bind_param('s', $ind);
        $stmt->execute();
        $row = fetch_one_stmt($stmt);
        if($row){
            $surn=$row->surname;
            $firstname=$row->firstname;
            $mname=$row->middlename;
            $phone=$row->phone;
            $nok=isset($row->nok) ? $row->nok : '';
            $nokph=isset($row->nok_contact) ? $row->nok_contact : '';
            $dob=isset($row->dob) ? $row->dob : '';
            $date=isset($row->reg_date) ? $row->reg_date : '';
            $pic=isset($row->picture) ? $row->picture : '';
        } else {
            $surn=''; $firstname=''; $mname=''; $phone=''; $nok=''; $nokph=''; $dob=''; $date=''; $pic='';
        }
        $stmt->close();
    }
}else{}

// If no row was loaded by the prefix-based checks, try fallbacks:
// - student by matric_no
// - staff by staff_no
if (!isset($row) || !$row) {
    // try student by matric_no
    $stmt = $mysqli->prepare("SELECT * FROM student WHERE matric_no = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $ind);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_object();
            $surn = isset($row->surname) ? $row->surname : '';
            $firstname = isset($row->firstname) ? $row->firstname : '';
            $mname = isset($row->middlename) ? $row->middlename : '';
            $phone = isset($row->phone) ? $row->phone : '';
            $cate = 'STUDENT CARD';
            $nok = isset($row->nok) ? $row->nok : '';
            $nokph = isset($row->nok_contact) ? $row->nok_contact : '';
            $dob = isset($row->dob) ? $row->dob : '';
            $date = isset($row->reg_date) ? $row->reg_date : '';
            $pic = isset($row->picture) ? $row->picture : '';
        }
        $stmt->close();
    }

    // try staff by staff_no
    if (!isset($row) || !$row) {
        $stmt = $mysqli->prepare("SELECT * FROM staff WHERE staff_no = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $ind);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_object();
                $surn = isset($row->surname) ? $row->surname : '';
                $firstname = isset($row->firstname) ? $row->firstname : '';
                $mname = isset($row->middlename) ? $row->middlename : '';
                $phone = isset($row->phone) ? $row->phone : '';
                $cate = 'STAFF CARD';
                $nok = isset($row->nok) ? $row->nok : '';
                $nokph = isset($row->nok_contact) ? $row->nok_contact : '';
                $dob = isset($row->dob) ? $row->dob : '';
                $date = isset($row->reg_date) ? $row->reg_date : '';
                $pic = isset($row->picture) ? $row->picture : '';
            }
            $stmt->close();
        }
    }
}

// Ensure variables exist to avoid 'attempt to read property on null' warnings
$surn = isset($surn) ? $surn : '';
$firstname = isset($firstname) ? $firstname : '';
$mname = isset($mname) ? $mname : '';
$phone = isset($phone) ? $phone : '';
$cate = isset($cate) ? $cate : '';
$nok = isset($nok) ? $nok : '';
$nokph = isset($nokph) ? $nokph : '';
$dob = isset($dob) ? $dob : '';
$date = isset($date) ? $date : '';
$pic = isset($pic) ? $pic : '';


		if(isset($_POST['Send_Signal']))
		{
           $time= date("h:i:sa" );
            $pat_code=$_POST['pat_code'];
            $rdate=date('Y-m-d');
            $pat_surn=$_POST['surn'];
			$pat_fname=$_POST['fname'];
			$pat_mname=$_POST['mname'];
			$pat_dob=$_POST['dob'];
            $category=$_POST['cate'];
            $status='Not Yet';
            
            $fname=$pat_surn." ".$pat_fname." ".$pat_mname;
            $pics=$pic;
            
            // Get campus location ID from session. Use NULL when not set so the DB stores NULL
            // for unassigned signals instead of 0.
            $campus_id = null;
            if (isset($_SESSION['working_location_id']) && $_SESSION['working_location_id'] !== '') {
                $campus_id = (int) $_SESSION['working_location_id'];
            }
            
            // Check if sendsignal table has campus_id column
            $hascamp = 0;
            $resCol = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id'");
            if ($resCol) {
                $rowCol = $resCol->fetch_assoc();
                $hascamp = isset($rowCol['cnt']) ? (int)$rowCol['cnt'] : 0;
            }
            
            // Insert campus_id only when the column exists AND we have a session campus set.
            // If the column exists but there's no session campus, omit campus_id so the DB will
            // store NULL (preferred over using 0 to indicate "unassigned").
            if ($hascamp && $campus_id !== null) {
                $query = "insert into sendsignal(pat_code,Fullname,Date,Time,Category,dob,picture,status,campus_id) values(?,?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('ssssssssi', $pat_code, $fname, $rdate, $time, $category, $pat_dob, $pics, $status, $campus_id);
            } elseif ($hascamp) {
                // Column exists but no campus in session: insert without campus_id column so it stays NULL
                $query = "insert into sendsignal(pat_code,Fullname,Date,Time,Category,dob,picture,status) values(?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('ssssssss', $pat_code, $fname, $rdate, $time, $category, $pat_dob, $pics, $status);
            } else {
                // Table has no campus_id column; fallback to original insert
                $query = "insert into sendsignal(pat_code,Fullname,Date,Time,Category,dob,picture,status) values(?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('ssssssss', $pat_code, $fname, $rdate, $time, $category, $pat_dob, $pics, $status);
            }
			$stmt->execute();
			/*
			*Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
			*echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
			*/ 
			//declare a varible which will be passed to alert function
			if($stmt)
			{
				$success = "Nursing Signal Sent Sucessfully";
			}
			else {
				$err = "Please Try Again Or Try Later";
			}
			
			
		}


       
?>
<!--End Server Side-->
<!--End Patient Registration-->
<!DOCTYPE html>
<html lang="en">
    
    <!--Head-->
    <?php include('assets/inc/head.php');?>
    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
            <?php include("assets/inc/nav_r.php");?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
            <?php include("assets/inc/sidebar_rec.php");?>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

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
                                            <li class="breadcrumb-item"><a href="record_dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Patients</a></li>
                                            <li class="breadcrumb-item active">Add Patient</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Send Nursing Signal</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        <!-- Form row -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                       
                                        <!--Add Patient Form-->
                                        <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" >
                                            <div class="form-row">
                                                <div class="form-group col-md-4" >
                                                  
                                                    <img src="picture/<?php echo $pic; ?>" width="200" height="200" style="border-radius:inherit;">
                                                </div>
                                               
                                                
                                                
                                            </div>
                                             <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Patient Individual Code</h3></label>
                                                    <input required="required" type="text" style="color:blue; font-size:x-large; background-color:grey;" value="<?php echo $ind;  ?>" name="pat_code" class="form-control" id="inputCity">
                                                </div>
                                               
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Registration Date</h3></label>
                                                    <input type="date" required="required" style="font-size:x-large;"  name="regdate" class="form-control" value="<?php echo $date;  ?>" id="inputEmail4" placeholder="DD/MM/YYYY">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Category</h3></label>
                                                    <input type="text" required="required" style="font-size:x-large;"  name="cate" class="form-control" value="<?php echo $cate;  ?>" id="inputEmail4" placeholder="DD/MM/YYYY">
                                                </div>
                                                
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label">Surname</label>
                                                    <input type="text" required="required" value="<?php echo $surn;?>" name="surn" class="form-control" id="inputEmail4" placeholder="Patient's First Name">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label">First Name</label>
                                                    <input type="text" required="required" value="<?php echo $firstname;?>" name="fname" class="form-control" id="inputEmail4" placeholder="Patient's First Name">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">Middle Name</label>
                                                    <input required="required" type="text" name="mname" value="<?php echo $mname;?>" class="form-control"  id="inputPassword4" placeholder="Patient`s Middle Name">
                                                </div>
                                            </div>

                                            
                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Date of Birth</label>
                                                    <input required="required" type="text" name="dob" value="<?php echo $dob;?>" class="form-control" id="inputCity">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Patient NOK</label>
                                                    <input required="required" type="text" name="nok" value="<?php echo $nok;?>" class="form-control" id="inputCity">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">NOK Mobile Number</label>
                                                    <input required="required" type="text" name="noknumber" value="<?php echo $nokph;?>" class="form-control" id="inputCity">
                                                </div>

                                                
                                                
                                                <div class="form-group col-md-2" style="display:none">
                                                    <?php 
                                                        $length = 5;    
                                                        $patient_number =  substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,$length);
                                                    ?>
                                                    <label for="inputZip" class="col-form-label">Patient Number</label>
                                                    <input type="text" name="pat_number" value="<?php echo $patient_number;?>" class="form-control" id="inputZip">
                                                </div>
                                            </div>

                                            <button type="submit" name="Send_Signal" class="ladda-button btn btn-primary" data-style="expand-right">Send Patient Signal</button>

                                        </form>
                                        <!--End Patient Form-->







                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->

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

        <!-- App js-->
        <script src="assets/js/app.min.js"></script>

        <!-- Loading buttons js -->
        <script src="assets/libs/ladda/spin.js"></script>
        <script src="assets/libs/ladda/ladda.js"></script>

        <!-- Buttons init js-->
        <script src="assets/js/pages/loading-btn.init.js"></script>
         <script src="scripts/jquery-1.9.1.min.js" type="text/javascript"></script>
  <script src="scripts/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
  <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
  <script src="scripts/flot/jquery.flot.js" type="text/javascript"></script>
  <script src="scripts/datatables/jquery.dataTables.js"></script>
  <script>
    $(document).ready(function() {
      $('.datatable-1').dataTable();
      $('.dataTables_paginate').addClass("btn-group datatable-pagination");
      $('.dataTables_paginate > a').wrapInner('<span />');
      $('.dataTables_paginate > a:first-child').append('<i class="icon-chevron-left shaded"></i>');
      $('.dataTables_paginate > a:last-child').append('<i class="icon-chevron-right shaded"></i>');
    } );
  </script>
    </body>

</html>