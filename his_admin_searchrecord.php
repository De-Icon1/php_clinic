<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');


        if(isset($_POST['search']))
        {
            $searching = isset($_POST['Searching']) ? trim($_POST['Searching']) : '';

            if($searching){
                $search = $searching;
                // If input already looks like a code with a known prefix, keep original behaviour
                if(strpos($search, 'IND') !== false || strpos($search, 'F') !== false || strpos($search, 'ST') !== false || strpos($search, 'S') !== false || strpos($search, 'H') !== false || strpos($search, 'A') !== false) {
                    echo "<script>location='his_admin_sendsignals.php?id=$search'</script>";
                    exit;
                }

                $results = array();
                $like = "%".$search."%";

                // Search students (by STcode or surname or code-like match)
                $stmt = $mysqli->prepare("SELECT STcode AS code, CONCAT(surname,' ',firstname) AS name, 'STUDENT' AS type FROM student WHERE STcode = ? OR STcode LIKE ? OR LOWER(surname) = LOWER(?) OR LOWER(surname) LIKE LOWER(?) OR matric_no = ? OR LOWER(matric_no) LIKE LOWER(?) LIMIT 10");
                if($stmt){
                    $stmt->bind_param('ssssss', $search, $like, $search, $like, $search, $like);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while($r = $res->fetch_assoc()){
                        $results[] = $r;
                    }
                    $stmt->close();
                }

                // Search staff
                $stmt = $mysqli->prepare("SELECT Scode AS code, CONCAT(surname,' ',firstname) AS name, 'STAFF' AS type FROM staff WHERE Scode = ? OR Scode LIKE ? OR LOWER(surname) = LOWER(?) OR LOWER(surname) LIKE LOWER(?) OR staff_no = ? OR LOWER(staff_no) LIKE LOWER(?) LIMIT 10");
                if($stmt){
                    $stmt->bind_param('ssssss', $search, $like, $search, $like, $search, $like);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while($r = $res->fetch_assoc()){
                        $results[] = $r;
                    }
                    $stmt->close();
                }

                // Search individual (general patients) by code or surname
                $stmt = $mysqli->prepare("SELECT code AS code, CONCAT(surname,' ',firstname) AS name, 'INDIVIDUAL' AS type FROM individual WHERE code = ? OR LOWER(surname) = LOWER(?) OR LOWER(surname) LIKE LOWER(?) LIMIT 10");
                if($stmt){
                    $stmt->bind_param('sss', $search, $search, $like);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while($r = $res->fetch_assoc()){
                        $results[] = $r;
                    }
                    $stmt->close();
                }

                // If exactly one match, redirect directly to sendsignals
                if(count($results) == 1){
                    $code = $results[0]['code'];
                    echo "<script>location='his_admin_sendsignals.php?id=$code'</script>";
                    exit;
                }

                // Otherwise we'll display matches below the form. Store in session for display.
                $_SESSION['search_results'] = $results;
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
                                    <h4 class="page-title">Searching Patient Record</h4>
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
                                        <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                             <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Search Patient (Code, Matric, Staff No or Surname)</h3></label>
                                                    <input required="required" type="text" style="color:blue;" placeholder="Enter code, matric, staff number, or surname" name="Searching" class="form-control" id="inputCity">
                                                </div>

                                                
                                                
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

                                            <button type="submit" name="search" class="ladda-button btn btn-primary" data-style="expand-right">Search Patient Record</button>

                                        </form>
                                        <!--End Patient Form-->

                                        <?php
                                        if(isset($_SESSION['search_results']) && is_array($_SESSION['search_results'])){
                                            $results = $_SESSION['search_results'];
                                            if(count($results) === 0){
                                                echo '<div class="alert alert-warning mt-3">No matches found for your search.</div>';
                                            } else {
                                                echo '<div class="mt-3">';
                                                echo '<h5>Search Results</h5>';
                                                echo '<ul class="list-group">';
                                                foreach($results as $r){
                                                    $code = htmlspecialchars($r['code']);
                                                    $name = htmlspecialchars($r['name']);
                                                    $type = htmlspecialchars($r['type']);
                                                    echo '<li class="list-group-item">';
                                                    echo '<a href="his_admin_sendsignals.php?id='.$code.'">'.$code.' - '.$name.' ('.$type.')</a>';
                                                    echo '</li>';
                                                }
                                                echo '</ul>';
                                                echo '</div>';
                                            }
                                            unset($_SESSION['search_results']);
                                        }
                                        ?>



                        <!-- end row -->


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