<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');

if(isset($_GET['del'])){

     //sql to insert captured values
    $id=$_GET['del'];
            $query="delete from scan where id=?";
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
                $success = "Scan Deleted Sussefully";
            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}

		if(isset($_POST['genreport']))
		{
        $datefrm=$_POST['datefrom'];
        $report_campus = isset($_POST['report_campus_id']) ? (int)$_POST['report_campus_id'] : 0;
        $camp_q = $report_campus ? "&report_campus_id={$report_campus}" : '';
        header("location:dailystock_report.php?datefrm=$datefrm{$camp_q}");

			
		}

        if(isset($_POST['phareport']))
        {
            $datefrm=$_POST['datefrom'];
            $report_campus = isset($_POST['report_campus_id']) ? (int)$_POST['report_campus_id'] : 0;
            $camp_q = $report_campus ? "&report_campus_id={$report_campus}" : '';
            header("location:phardailystock_report.php?datefrm=$datefrm{$camp_q}");

            
        }
        
        



function getscans(){
  
$sql="SELECT * FROM scan ORDER BY id DESC "; 
$stmt= $mysqli->prepare($sql) ;
    $stmt->execute() ;
    $res=$stmt->get_result();
$cnt=1;
while($reply=$res->fetch_object()){
                                                            
 echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";

      } 
    
}
function getscan($mysqli){
    $sql = "SELECT * FROM scan ORDER BY id ASC";
    $result = mysqli_query($mysqli,$sql);
    while($reply = mysqli_fetch_array($result)){
        echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";
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
                                    <h4 class="page-title">Daily Store Stock Report</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        <!-- Form row -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                       
                                            <?php
                                            // Add campus selector for this report
                                            if (isset($mysqli)) {
                                                $campuses = [];
                                                $candidateTables = ['campus_locations','campuses','locations','his_campus'];
                                                foreach ($candidateTables as $ct) {
                                                    $q = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $mysqli->real_escape_string($ct) . "'";
                                                    $r = $mysqli->query($q);
                                                    if ($r && (int)$r->fetch_assoc()['cnt'] > 0) {
                                                        $cols = [];
                                                        $colRes = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $mysqli->real_escape_string($ct) . "'");
                                                        if ($colRes) { while ($c = $colRes->fetch_assoc()) { $cols[] = $c['COLUMN_NAME']; } }
                                                        $labelCols = ['name','title','campus_name','campus','location','site'];
                                                        $label = null; foreach ($labelCols as $lc) { if (in_array($lc,$cols)) { $label=$lc; break; } }
                                                        if ($label) { $safeLabel = $mysqli->real_escape_string($label); $res = $mysqli->query("SELECT id, `".$safeLabel."` AS name FROM " . $ct . " ORDER BY `".$safeLabel."` ASC"); }
                                                        else { $res = $mysqli->query("SELECT id FROM " . $ct); }
                                                        if ($res) { while ($row=$res->fetch_assoc()) { $campuses[] = ['id'=>$row['id'],'name'=> isset($row['name'])? $row['name']: 'Campus '. $row['id'] ]; } }
                                                        break;
                                                    }
                                                }
                                                if (empty($campuses)) {
                                                    $seen=[]; $tables=['pharmacy','store_stock','pharmacy_stock'];
                                                    foreach($tables as $t){ $q = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $mysqli->real_escape_string($t) . "' AND COLUMN_NAME='campus_id'"; $r=$mysqli->query($q); if($r && (int)$r->fetch_assoc()['cnt']>0){ $res=$mysqli->query("SELECT DISTINCT campus_id FROM " . $t . " WHERE campus_id IS NOT NULL ORDER BY campus_id ASC"); if($res){ while($row=$res->fetch_assoc()){ $id=(int)$row['campus_id']; if($id && !in_array($id,$seen)){ $seen[]=$id; $campuses[]=['id'=>$id,'name'=>'Campus '.$id]; } } } } }
                                                }
                                                if (!empty($campuses)){
                                                    $sel = isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : 0;
                                                    echo '<div style="margin-bottom:10px;"><label>Campus</label><select id="campus_select" class="form-control" style="max-width:240px;">';
                                                    echo '<option value="0"'.($sel===0?' selected':'').'>All campuses</option>';
                                                    foreach($campuses as $c){ $s = ($sel===(int)$c['id'])? ' selected': ''; echo '<option value="'.htmlspecialchars($c['id']).'"'.$s.'>'.htmlspecialchars($c['name']).'</option>'; }
                                                    echo '</select></div>';
                                                    ?>
                                                    <script>
                                                    (function(){var sel=document.getElementById('campus_select'); if(!sel) return; sel.addEventListener('change',function(){ var fd=new FormData(); fd.append('campus_id', this.value); fetch('assets/inc/set_campus.php',{method:'POST',body:fd}).then(r=>r.json()).then(j=>{ if(j.success) location.reload(); else alert('Could not set campus: '+j.msg); }).catch(()=>alert('Request failed')); });})();
                                                    </script>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        
                                        <!--Add Patient Form-->
                                        <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                             <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Select Date</h3></label>
                                                    <input type="date" required="required" name="datefrom" class="form-control" id="inputEmail4" placeholder="DD/MM/YYYY">
                                                </div>

                                                
                                                
                                            </div>
                                            
                                            


                                            <button type="submit" name="genreport" class="ladda-button btn btn-primary" >Process Store Stock Report</button>

                                            <button type="submit" name="phareport" class="ladda-button btn btn-primary" >Process Pharmacy Stock Report</button>


                                        </form>
<hr>

                               

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
        <script>
    // Grab elements
   /* const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    const snapButton = document.getElementById('snap');

    // Request access to webcam
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => {
        video.srcObject = stream;
      })
      .catch(err => {
        console.error("Error accessing webcam:", err);
      });

    // Capture a frame from the video
    snapButton.addEventListener('click', () => {
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
    });*/

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    const snap = document.getElementById('snap');

    // Start webcam stream
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => video.srcObject = stream)
      .catch(err => console.error("Webcam error:", err));

    snap.addEventListener('click', () => {
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
      const imageData = canvas.toDataURL('image/png'); // Base64 encoded PNG



      fetch('save_image.php', {
        method: 'POST',
        body: JSON.stringify({ image: imageData }),
        headers: { 'Content-Type': 'application/json' }
      })
  
     // .then(response => response.text())
      //.then(data => alert(data))
      .catch(error => console.error('Error:', error));
    });

  </script>
    </body>

</html>