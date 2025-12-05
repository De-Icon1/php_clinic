<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
     $ind=$_GET['id'];

     if (strpos($ind, 'IND') !== false) {

    $ret="SELECT * FROM individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $row=$res->fetch_object();
    $surn=$row->surname;
    $firstname=$row->firstname;
    $mname=$row->middlename;
    $phone=$row->phone;
    $cate="INDIVIDUAL CARD";
    $nok=$row->nok;
    $nokph=$row->nok_contact;
     $dob=$row->dob;
    $date=$row->reg_date;
    $pic=$row->picture;
}
elseif (strpos($ind, 'F') !== false) {
    $ret="SELECT * FROM family_individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $row=$res->fetch_object();
    $surn=$row->surname;
    $firstname=$row->firstname;
    $mname=$row->middlename;
     $cate="FAMILY CARD";
    $phone=$row->phone;
    $nok=$row->nok;
    $nokph=$row->nok_contact;
    $dob=$row->dob;
    $date=$row->reg_date;
    $pic=$row->picture;
}
elseif (strpos($ind, 'ST') !== false) {
    $ret="SELECT * FROM student where STcode='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $row=$res->fetch_object();
    $surn=$row->surname;
    $firstname=$row->firstname;
    $mname=$row->middlename;
     $cate="STUDENT CARD";
    $phone=$row->phone;
    $nok=$row->nok;
    $nokph=$row->nok_contact;
    $dob=$row->dob;
    $date=$row->reg_date;
    $pic=$row->picture;
}
elseif (strpos($ind, 'S') !== false) {
    $ret="SELECT * FROM staff where Scode='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $row=$res->fetch_object();
    $surn=$row->surname;
    $firstname=$row->firstname;
     $cate="STAFF CARD";
    $mname=$row->middlename;
    $phone=$row->phone;
    $nok=$row->nok;
    $nokph=$row->nok_contact;
    $dob=$row->dob;
    $date=$row->reg_date;
    $pic=$row->picture;
}
elseif (strpos($ind, 'H') !== false) {
    $ret="SELECT * FROM hmocompany_individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $row=$res->fetch_object();
    $surn=$row->surname;
    $firstname=$row->firstname;
    $mname=$row->Lastname;
    $phone=$row->phone;
     $cate="HMO CARD";
    $nok=$row->nok;
    $nokph=$row->nok_phone;
    $dob=$row->dob;
    $date=$row->reg_date;
    $pic=$row->picture;
}
elseif (strpos($ind, 'A') !== false) {
    $ret="SELECT * FROM individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $row=$res->fetch_object();
    $surn=$row->surname;
    $firstname=$row->firstname;
    $mname=$row->middlename;
    $phone=$row->phone;
    $nok=$row->nok;
    $nokph=$row->nok_contact;
    $dob=$row->dob;
    $date=$row->reg_date;
    $pic=$row->picture;
}else{}


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

			$query="insert into sendsignal(pat_code,Fullname,Date,Time,Category,dob,picture,status) values(?,?,?,?,?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			$rc=$stmt->bind_param('ssssssss',$pat_code,$fname,$rdate,$time,$category, $pat_dob,$pics,$status);
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