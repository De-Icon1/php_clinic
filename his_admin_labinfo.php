<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
     $ind=$_GET['id'];

     $ind = $_GET['ind'] ?? '';  // or however you receive it

// Helper function to fetch patient info
function fetch_patient($mysqli, $table, $field, $ind) {
    $sql  = "SELECT * FROM $table WHERE $field = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $ind);
    $stmt->execute();
    $res  = $stmt->get_result();
    return $res->fetch_object();
}

$row = null;   // default

if (strpos($ind, 'IND') !== false) {
    $row = fetch_patient($mysqli, 'individual', 'code', $ind);
    $cate = "INDIVIDUAL CARD";
}
elseif (strpos($ind, 'F') !== false) {
    $row = fetch_patient($mysqli, 'family_individual', 'code', $ind);
    $cate = "FAMILY CARD";
}
elseif (strpos($ind, 'ST') !== false) {
    $row = fetch_patient($mysqli, 'student', 'STcode', $ind);
    $cate = "STUDENT CARD";
}
elseif (strpos($ind, 'S') !== false) {
    $row = fetch_patient($mysqli, 'staff', 'Scode', $ind);
    $cate = "STAFF CARD";
}
elseif (strpos($ind, 'H') !== false) {
    $row = fetch_patient($mysqli, 'hmocompany_individual', 'code', $ind);
    $cate = "HMO CARD";
}
elseif (strpos($ind, 'A') !== false) {
    $row = fetch_patient($mysqli, 'individual', 'code', $ind);
    $cate = "ANTENATAL CARD";
}

// ✅ Now verify we actually found something
if ($row) {
    $surn      = $row->surname ?? '';
    $firstname = $row->firstname ?? '';
    $mname     = $row->middlename ?? '';
    $phone     = $row->phone ?? '';
    $nok       = $row->nok ?? '';
    $nokph     = $row->nok_contact ?? $row->nok_phone ?? '';
    $dob       = $row->dob ?? '';
    $date      = $row->reg_date ?? '';
    $pic       = $row->picture ?? '';
} else {
    // handle not found
    $surn = $firstname = $mname = $phone = $nok = $nokph = $dob = $date = $pic = '';
    $cate = "UNKNOWN CARD";
    $err  = "No record found for patient code: $ind";
}

if (isset($_POST['Send_Lab'])) {
    $time      = date("H:i:s");
    $pat_code  = $_POST['pat_code'];
    $rdate     = date('Y-m-d');
    $pat_surn  = $_POST['surn'];
    $pat_fname = $_POST['fname'];
    $pat_mname = $_POST['mname'];
    $category  = $_POST['cate'];
    $test      = $_POST['test'];
    $result    = '';

    $fname = trim("$pat_surn $pat_fname $pat_mname");
    $pics  = $pic;

    $query = "INSERT INTO patient_lab(date, code, name, test, result, category)
              VALUES(?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ssssss', $rdate, $pat_code, $fname, $test, $result, $category);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Laboratory Test Sent Successfully";
    } else {
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
                        <?php include("assets/inc/slidebar_lab.php");?>
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
                                    <h4 class="page-title">Register Patient For Test</h4>
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
                                                    <input required="required" type="text" name="lname" value="<?php echo $mname;?>" class="form-control"  id="inputPassword4" placeholder="Patient`s Middle Name">
                                                </div>
                                            </div>

                                            
                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Lab Date</label>
                                                    <input required="required" type="text" name="dob" value="<?php echo $date=date('Y-m-d'); ?>" class="form-control" id="inputCity">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Select Lab Test</label>
                                                    <select id="inputState" required="required" onChange="getscan(this.value); " name="scan"  class="form-control">
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
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Selected Lab Test</label>
                                                    <textarea class="form-control" id="sname" name="test" rows="3">
                                                              </textarea>
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

                                            <button type="submit" name="Send_Lab" class="ladda-button btn btn-primary" data-style="expand-right">Register Test</button>

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

    function getscan(val){
    $.ajax({
    type: "POST",
    url: "get_scan.php",
    data:'name='+val,
    success: function(data){
        $("#sname").html(data);
    }
    });
}

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