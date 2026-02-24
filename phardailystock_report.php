<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
    $datefrm=$_GET['datefrm'];
    // campus scoping - prefer per-report override `report_campus_id` then session
    $report_campus = isset($_GET['report_campus_id']) ? (int)$_GET['report_campus_id'] : null;
    $campus_id = $report_campus ? $report_campus : (isset($_SESSION['campus_id']) ? (int)$_SESSION['campus_id'] : null);
    function table_has_campus($mysqli, $table)
    {
        $t = $mysqli->real_escape_string($table);
        $q = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $t . "' AND COLUMN_NAME='campus_id'";
        $r = $mysqli->query($q);
        if ($r) {
            $row = $r->fetch_assoc();
            return (int) $row['cnt'] > 0;
        }
        return false;
    }
    $pharm_has_campus = table_has_campus($mysqli, 'pharmacy_stock');
    $date=date('Y-m-d');
    pharmacyclosingstock($date,$mysqli);
    storeclosingstock($date,$mysqli);

if(isset($_GET['del'])){

     //sql to insert captured values
    $id=$_GET['del'];
            $query="delete from drug where id=?";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('s',$id);
            $stmt->execute();
            /*
            *Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
            *echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
            */ 
            //declare a varible which will be passed to alert function
            if($stmt)
            {
                $success = "Drug Deleted Sussefully";
            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}

		
function pharmacyclosingstock($date,$mysqli){
    $sql="SELECT * FROM pharmacy_stock where date='$date'"; 
   $result = mysqli_query($mysqli,$sql);
    $num=mysqli_num_rows($result);
        if($num>0){

            $sqll="SELECT * FROM pharmacy order by id ASC"; 
            $results = mysqli_query($mysqli,$sqll);
            while($reply = mysqli_fetch_array($results)){
                $name=$reply['name'];
                $qnt=$reply['quantity'];
                $quey="update pharmacy_stock set closing='$qnt' where name='$name' and date='$date'";
                $st2 = mysqli_query($mysqli,$quey);
                           
                        }
            }
        }
        

function storeclosingstock($date,$mysqli){
    $sql="SELECT * FROM store_stock where date='$date'"; 
   $result = mysqli_query($mysqli,$sql);
    $num=mysqli_num_rows($result);
    if($num>0){
        $sqll="SELECT * FROM drug order by id ASC"; 
        $results = mysqli_query($mysqli,$sqll);
        while($reply = mysqli_fetch_array($results)){
                $name=$reply['name'];
                $qnt=$reply['quantity'];
                $quey="update store_stock set closing='$qnt' where name='$name' and date='$date'";
                $st2 = mysqli_query($mysqli,$quey);
                           
                }
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
            <?php include("assets/inc/sidebar_admin.php");?>
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
                                            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Patients</a></li>
                                            <li class="breadcrumb-item active">Add Patient</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Daily Stock of Pharmacy Report of <?php echo $datefrm;?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        <!-- Form row -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                       
                                        

<div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <div class="mb-2">
                                        <div class="row">
                                            <div class="col-12 text-sm-center form-inline" >
                                                <div class="form-group mr-2" style="display:none">
                                                    <select id="demo-foo-filter-status" class="custom-select custom-select-sm">
                                                        <option value="">Show all</option>
                                                        <option value="Discharged">Discharged</option>
                                                        <option value="OutPatients">OutPatients</option>
                                                        <option value="InPatients">InPatients</option>
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
                                                <th data-hide="phone" style="color:white;">Date</th>                                              
                                                <th data-hide="phone" style="color:white;">Drug Name</th>
                                                <th data-hide="phone" style="color:white;">Opening Stock</th>
                                                <th data-hide="phone" style="color:white;">Add Stock</th>
                                                <th data-hide="phone" style="color:white;">Stock Out</th>
                                                <th data-hide="phone" style="color:white;">Closing Stock</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                // Apply campus filter when available
                                                if ($pharm_has_campus && $campus_id) {
                                                    $ret = "SELECT * FROM pharmacy_stock WHERE date = ? AND campus_id = ? ORDER BY name ASC";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param('si', $datefrm, $campus_id);
                                                    $stmt->execute();
                                                    $res = $stmt->get_result();
                                                } else {
                                                    $ret = "SELECT * FROM pharmacy_stock WHERE date = ? ORDER BY name ASC";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param('s', $datefrm);
                                                    $stmt->execute();
                                                    $res = $stmt->get_result();
                                                }
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $cnt?></td>
                                                    <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->name;?></td>
                                                    <td><?php echo $row->opening;?></td>
                                                    <td><?php echo $row->addstock;?></td>
                                                    <td><?php $add=$row->addstock; $opn=$row->opening; $clos=$row->closing; $tot=$add + $opn; echo $tot-$clos;?></td>
                                                    <td><?php echo $row->closing;?></td>

                                                    
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
                                        </table>
                                    </div> <!-- end .table-responsive-->
                                </div> <!-- end card-box -->

                                <button type="submit" onClick="window.print()" class="ladda-button btn btn-primary" >Print Report</button>
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

</html>yy