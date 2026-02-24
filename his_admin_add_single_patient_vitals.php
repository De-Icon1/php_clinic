<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
		if(isset($_POST['add_patient_vitals'])){
			$name = $_POST['name'];
    $vit_pat_number = $_GET['pat_number'];
    $vit_bodytemp = $_POST['vit_bodytemp'] . "°C";
    $vit_heartpulse = $_POST['vit_heartpulse'] . "P";
    $vit_resprate = $_POST['vit_resprate'] . "R";
    $vit_bloodpress = $_POST['vit_bloodpress'] . "mmHg";
    $vit_sugarlevel = $_POST['vit_sugarlevel'] . "mg/dL";
    $vit_Spo2 = $_POST['vit_Spo2'] . "%";
    $vit_height = $_POST['vit_height'] . "cm";
    $vit_weight = $_POST['vit_weight'] . "kg";
    $pic = $_GET['pic'];
    $status = 'Checked';
    $dated = date('Y-m-d'); // Used for sendsignal table
    $current_time = date('Y-m-d H:i:s'); // Precise record timestamp

    $query = "INSERT INTO his_vitals 
              (date, fullname, vit_pat_number, vit_bodytemp, vit_heartpulse, vit_resprate, vit_bloodpress, vit_sugarlevel, vit_Spo2, vit_height, vit_weight, vit_daterec, picture)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sssssssssssss',
        $dated,
        $name,
        $vit_pat_number,
        $vit_bodytemp,
        $vit_heartpulse,
        $vit_resprate,
        $vit_bloodpress,
        $vit_sugarlevel,
        $vit_Spo2,
        $vit_height,
        $vit_weight,
        $current_time,   // Use current timestamp, not $_POST['datet']
        $pic
    );
    $stmt->execute();

    // Update sendsignal
    $query2 = "UPDATE sendsignal SET status=? WHERE pat_code=? AND Date=?";
    $stmts = $mysqli->prepare($query2);
    $stmts->bind_param('sss', $status, $vit_pat_number, $dated);
    $stmts->execute();

    if ($stmt) {
        $success = "Patient vitals added successfully.";
    } else {
        $err = "Please try again or contact admin.";
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
            <?php include("assets/inc/slidebar_nur.php");?>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            <?php
                $pat_number = $_GET['pat_number'];
                $status='Not Yet';
                $date=date("Y-m-d");
                $ret="SELECT  * FROM sendsignal WHERE pat_code=? and Date=? and status=?";
                $stmt= $mysqli->prepare($ret) ;
                $stmt->bind_param('sss',$pat_number,$date,$status);
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
                                        <h4 class="page-title">Capture Vitals</h4>
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
                                                  
                                                    <img src="picture/<?php echo $row->picture; ?>" width="200" height="200" style="border-radius:inherit;">
                                                </div>
                                               
                                                     </div>
                                                <div class="form-row">



                                                    <div class="form-group col-md-6">
                                                        <label for="inputEmail4" class="col-form-label">Patient Name</label>
                                                        <input type="text" style="color:blue; font-size:x-large; background-color:grey;" required="required" readonly name="name" value="<?php echo $row->Fullname;?>" class="form-control" id="inputEmail4" placeholder="Patient's First Name">
                                                    </div>

                                                    <div class="form-group col-md-6">
                                                        <label for="inputPassword4" class="col-form-label">Patient Date and Time Visit</label>
                                                        <input required="required" type="text" style="color:blue; font-size:x-large; background-color:grey;" readonly name="datet" value="<?php echo $row->Date; echo $row->Time;?>" class="form-control"  id="inputPassword4" placeholder="Patients Date Time">
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
                                                        <label for="inputEmail4" class="col-form-label">Patient Body Temperature °C</label>
                                                        <input type="text" required="required"  name="vit_bodytemp"class="form-control" id="inputEmail4" placeholder="°C">
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="inputPassword4" class="col-form-label">Patient Heart Pulse/Beat BPM</label>
                                                        <input required="required" type="text"  name="vit_heartpulse"  class="form-control"  id="inputPassword4" placeholder="HeartBeats Per Minute ">
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="inputPassword4" class="col-form-label">Patient Respiratory Rate bpm</label>
                                                        <input required="required" type="text"  name="vit_resprate"  class="form-control"  id="inputPassword4" placeholder="Breathes Per Minute">
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="inputPassword4" class="col-form-label">Patient Blood Pressure mmHg</label>
                                                        <input required="required" type="text"  name="vit_bloodpress"  class="form-control"  id="inputPassword4" placeholder="mmHg">
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="inputPassword4" class="col-form-label">Patient Sugar Level mg/dL</label>
                                                        <input required="required" type="text"  name="vit_sugarlevel"  class="form-control"  id="inputPassword4" placeholder="mg/dL">
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="inputPassword4" class="col-form-label">Patient SpO2 %</label>
                                                        <input required="required" type="text"  name="vit_Spo2"  class="form-control"  id="inputPassword4" placeholder="%">
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="inputPassword4" class="col-form-label">Patient Height cm</label>
                                                        <input required="required" type="text"  name="vit_height"  class="form-control"  id="inputPassword4" placeholder="cm">
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="inputPassword4" class="col-form-label">Patient Weight kg</label>
                                                        <input required="required" type="text"  name="vit_weight"  class="form-control"  id="inputPassword4" placeholder="kg">
                                                    </div>
                                                    
                                                </div>

                                                <button type="submit" name="add_patient_vitals" class="ladda-button btn btn-success" data-style="expand-right">Add Vitals</button>

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