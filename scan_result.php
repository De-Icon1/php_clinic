<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
     $ind=$_GET['pat_number'];

     if (strpos($ind, 'IND') !== false) {

    $ret="SELECT * FROM individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $rows=$res->fetch_object();
    $pic=$rows->picture;
}
elseif (strpos($ind, 'F') !== false) {
    $ret="SELECT * FROM family_individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $rows=$res->fetch_object();
    $pic=$rows->picture;
}
elseif (strpos($ind, 'C') !== false) {
    $ret="SELECT * FROM company_individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $rows=$res->fetch_object();
    $pic=$rows->picture;
}
elseif (strpos($ind, 'H') !== false) {
    $ret="SELECT * FROM hmocompany_individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $rows=$res->fetch_object();
    $pic=$rows->picture;
}
elseif (strpos($ind, 'A') !== false) {
    $ret="SELECT * FROM individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $rows=$res->fetch_object();
      $pic=$rows->picture;
}else{}

		if(isset($_POST['post_result']))
		{
			$name = $_POST['name'];
            $date = $_POST['datet'];
            $tests = $_POST['test'];
			$result = $_POST['result'];
            $st='';

			$query="Update patient_scan set result=? where code=? and test=? and result=?";
			$stmt = $mysqli->prepare($query);
			$rc=$stmt->bind_param('ssss',$result, $ind, $tests, $st);
			$stmt->execute();

                    

			/*
			*Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
			*echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
			*/ 
			//declare a varible which will be passed to alert function
			if($stmt)
			{
				$success = "Patient Result Posted Successfully";
                header("location: scan_resultlist.php");
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
            <?php include("assets/inc/nav_n.php");?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
            <?php include("assets/inc/slidebar_scan.php");?>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            <?php
                $pat_number = $_GET['pat_number'];
                $status='';
                $date=date("Y-m-d");
                $ret="SELECT  * FROM patient_scan WHERE code=? and result=?";
                $stmt= $mysqli->prepare($ret) ;
                $stmt->bind_param('ss',$pat_number,$status);
                $stmt->execute() ;//ok
                $res=$stmt->get_result();
                //$cnt=1;
                while($row=$res->fetch_object())
                {
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
                                                <li class="breadcrumb-item"><a href="nursing_dashboard.php">Dashboard</a></li>
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vital Sign</a></li>
                                                <li class="breadcrumb-item active">Capture Vitals</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">PATIENT TEST RESULT PORTAL</h4>
                                    </div>
                                </div>
                            </div>     
                            <!-- end page title --> 
                            <!-- Form row -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="header-title"></h4>
                                            <!--Add Patient Form-->
                                            <form method="post">
                                                 <div class="form-group col-md-6">
                                                    <div class="form-group col-md-4" >
                                                  
                                                    <img src="picture/<?php echo $pic; ?>" width="200" height="200" style="border-radius:inherit;">
                                                </div>
                                               
                                                     </div>
                                                <div class="form-row">



                                                    <div class="form-group col-md-6">
                                                        <label for="inputEmail4" class="col-form-label">Patient Name</label>
                                                        <input type="text" style="color:blue; font-size:x-large; background-color:grey;" required="required" readonly name="name" value="<?php echo $row->name;?>" class="form-control" id="inputEmail4" placeholder="Patient's First Name">
                                                    </div>

                                                    <div class="form-group col-md-6">
                                                        <label for="inputPassword4" class="col-form-label">Patient Test Date</label>
                                                        <input required="required" type="text" style="color:blue; font-size:x-large; background-color:grey;" readonly name="datet" value="<?php echo $row->date;?>" class="form-control"  id="inputPassword4" placeholder="Patients Date Time">
                                                    </div>

                                                </div>

                                                

                                                
                                                <hr>
                                                <div class="form-row">
                                                    
                                            
                                                    <div class="form-group col-md-2" style="display:none">
                                                        <?php 
                                                            $length = 5;    
                                                            $vit_no =  substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,$length);
                                                        ?>
                                                        <label for="inputZip" class="col-form-label">Vital Number</label>
                                                        <input type="text" name="vit_number" value="<?php echo $vit_no;?>" class="form-control" id="inputZip">
                                                    </div>
                                                </div>

                                                <div class="form-row">

                                                    <div class="form-group col-md-3">
                                                        <label for="inputEmail4" class="col-form-label">Patient Scan Test</label>
                                                        <input type="text" required="required" value="<?php echo $test=$_GET['test'];  ?>"  name="test"class="form-control" id="inputEmail4" placeholder="°C">
                                                    </div>

                                                    <div class="form-group col-md-12">
                                                        <label for="inputPassword4" class="col-form-label">Test Result</label>
                                                        <textarea class="form-control" id="sname" name="result" rows="9">
                                                              </textarea>
                                                    </div>

                                                    
                                                </div>

                                                <button type="submit" name="post_result" class="ladda-button btn btn-success" data-style="expand-right">Post Result</button>

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
            <?php }?>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

       
        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>
        <script src="//cdn.ckeditor.com/4.6.2/basic/ckeditor.js"></script>
        <script type="text/javascript">
        CKEDITOR.replace('editor')
        </script>

        <!-- Vendor js -->
        <script src="assets/js/vendor.min.js"></script>

        <!-- App js-->
        <script src="assets/js/app.min.js"></script>

        <!-- Loading buttons js -->
        <script src="assets/libs/ladda/spin.js"></script>
        <script src="assets/libs/ladda/ladda.js"></script>

        <!-- Buttons init js-->
        <script src="assets/js/pages/loading-btn.init.js"></script>
        
    </body>

</html>