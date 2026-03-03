<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
		if(isset($_POST['add_patient']))
		{
            $pat_code=$_POST['pat_code'];
            $date=$_POST['regdate'];
            $pat_surn=$_POST['surn'];
			$pat_fname=$_POST['fname'];
			$pat_lname=$_POST['lname'];
			$pat_dob=$_POST['dob'];
            $pat_phone=$_POST['phone'];
            $pat_addr=$_POST['add'];
            $pat_age = $_POST['age'];
            $nok=$_POST['nok'];
            $noknumber=$_POST['noknumber'];
             $mstatus=$_POST['mstatus'];
            $pics=$_FILES["pics"]["name"];

//$dir="productimages";
//unlink($dir.'/'.$pimage);


                 move_uploaded_file($_FILES["pics"]["tmp_name"],"picture/".$_FILES["pics"]["name"]);
   
			$query="insert into individual(surname,firstname,lastname,code,reg_date,dob,age,address,phone,nok,nok_contact,marrital,picture) values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$stmt = $mysqli->prepare($query);
			$rc=$stmt->bind_param('sssssssssssss',$pat_surn,$pat_fname, $pat_lname,$pat_code,$date, $pat_dob, $pat_age, $pat_addr,$pat_phone, $nok, $noknumber,$mstatus,$pics);
			$stmt->execute();
			/*
			*Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
			*echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
			*/ 
			//declare a varible which will be passed to alert function
			if($stmt)
			{
				$success = "Individual Registration Sussefully";
			}
			else {
				$err = "Please Try Again Or Try Later";
			}
			
			
		}


        $ind="IND"."4".rand(0,7729); 
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
                                    <h4 class="page-title">TODAYS PATIENT VISIT</h4>
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
                                       
                                        <!--End Patient Form-->


<div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h4 class="header-title">List of Visited Patients </h4>
                                    <div class="mb-2">
                                        <div class="row">
                                            <div class="col-12 text-sm-center form-inline" >
                                                <div class="form-group mr-2">
                                                    <label for="status-filter" class="mr-2">Filter by Status:</label>
                                                    <select id="status-filter" name="status_filter" class="custom-select custom-select-sm">
                                                        <option value="Not Yet" selected>Pending (Not Yet Checked)</option>
                                                        <option value="Checked">Checked</option>
                                                        <option value="All">All Patients</option>
                                                    </select>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                            <thead>
                                            <tr>
                                                <th style="color:white;">S/N</th>

                                                <th data-hide="phone" style="color:white;">Patient ID</th>
                                                <th data-hide="phone" style="color:white;">Fullname</th>
                                                <th data-hide="phone" style="color:white;">Visit Date</th>
                                                <th data-hide="phone" style="color:white;">Time</th>
                                                <th data-hide="phone" style="color:white;">Card Category</th>
                                                <th data-hide="phone" style="color:white;">Status</th>
                                                <th data-hide="phone" style="color:white;">Action</th>
                                              
                                                                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of all patients for today's visit
                                                *Filter by campus location to show only patients from user's location
                                                *Use working_location_id from session (set during login in index.php)
                                            */
                                            $rdate=date('Y-m-d');
                                            
                                            // Get status filter from URL or POST (default to "Not Yet")
                                            $status = isset($_GET['status_filter']) ? $_GET['status_filter'] : (isset($_POST['status_filter']) ? $_POST['status_filter'] : 'Not Yet');
                                            
                                            // Get campus/location from session (prefer working_location_id, fall back to campus_id if numeric)
                                            if (isset($_SESSION['working_location_id']) && is_numeric($_SESSION['working_location_id'])) {
                                                $campus_id = (int) $_SESSION['working_location_id'];
                                            } elseif (isset($_SESSION['campus_id']) && is_numeric($_SESSION['campus_id'])) {
                                                $campus_id = (int) $_SESSION['campus_id'];
                                            } else {
                                                $campus_id = null;
                                            }
                                            
                                            // Check if sendsignal table has campus_id column
                                            $hascamp = 0;
                                            $resCol = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id'");
                                            if ($resCol) {
                                                $rowCol = $resCol->fetch_assoc();
                                                $hascamp = isset($rowCol['cnt']) ? (int)$rowCol['cnt'] : 0;
                                            }
                                            
                                            // Build query with campus/location scoping
                                            // If the sendsignal table has a campus_id column we must strictly scope to the staff's campus.
                                            if ($hascamp) {
                                                if ($campus_id) {
                                                    // Strict: only show records assigned to this campus
                                                    if ($status === 'All') {
                                                        $ret = "SELECT * FROM sendsignal WHERE Date = ? AND campus_id = ? ORDER BY id DESC";
                                                        $stmt = $mysqli->prepare($ret);
                                                        $stmt->bind_param('si', $rdate, $campus_id);
                                                    } else {
                                                        $ret = "SELECT * FROM sendsignal WHERE Date = ? AND status = ? AND campus_id = ? ORDER BY id DESC";
                                                        $stmt = $mysqli->prepare($ret);
                                                        $stmt->bind_param('ssi', $rdate, $status, $campus_id);
                                                    }
                                                } else {
                                                    // Column exists but user has no campus assigned: show no records (safety)
                                                    $ret = "SELECT * FROM sendsignal WHERE 1 = 0";
                                                    $stmt = $mysqli->prepare($ret);
                                                }
                                            } else {
                                                // No campus column in sendsignal: fall back to Date/status only
                                                if ($status === 'All') {
                                                    $ret = "SELECT * FROM sendsignal WHERE Date = ? ORDER BY id DESC";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param('s', $rdate);
                                                } else {
                                                    $ret = "SELECT * FROM sendsignal WHERE Date = ? AND status = ? ORDER BY id DESC";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param('ss', $rdate, $status);
                                                }
                                            }
                                            
                                            if ($stmt) {
                                                $stmt->execute();
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $row->id;?></td>
                                                    <td><?php echo $row->pat_code;?></td>
                                                    <td><?php echo $row->Fullname;?></td>
                                                    <td><?php echo $row->Date;?></td>
                                                    <td><?php echo $row->Time;?></td>
                                                    <td><?php echo $row->Category;?></td>
                                                    <td style="color:red;"><?php echo $row->status?></td>
                                                    <td><a href="his_admin_add_single_patient_vitals.php?pat_number=<?php echo $row->pat_code;?>&pic=<?php echo $row->picture;?>" class="badge badge-success"><i class="far fa-eye "></i>Vitals</a></td>

                                                   
                                                </tr>
                                                </tbody>
                                            <?php  $cnt = $cnt +1 ; }
                                            } // Close if($stmt)
                                            ?>
                                            <tfoot>
                                            <tr class="active">
                                                <td colspan="8">
                                                    <div class="text-right">
                                                        <ul class="pagination pagination-rounded justify-content-end footable-pagination m-t-10 mb-0"></ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div> <!-- end .table-responsive-->
                                </div> <!-- end card-box -->
                            </div> <!-- end col -->
                        </div>

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
      
      // Status filter handler
      $('#status-filter').on('change', function() {
        var selected_status = $(this).val();
        window.location.href = '?status_filter=' + selected_status;
      });
    } );
  </script>
  <script type="text/javascript">
   function husbandURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        $('#hpass').attr('src', e.target.result);
       }
        reader.readAsDataURL(input.files[0]);
       }
    }
    
function husbandwURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        $('#hwpass').attr('src', e.target.result);
       }
        reader.readAsDataURL(input.files[0]);
       }
    }
    
 function wifeURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        $('#wpa').attr('src', e.target.result);
       }
        reader.readAsDataURL(input.files[0]);
       }
    }
 function wifewURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        $('#wwpa').attr('src', e.target.result);
       }
        reader.readAsDataURL(input.files[0]);
       }
    }
    

</script>
    </body>

</html>