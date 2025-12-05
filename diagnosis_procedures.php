<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
    include('assets/inc/functions.php');

if(isset($_GET['del'])){

     //sql to insert captured values
    $id=$_GET['del'];
            $query="delete from diagnosis where id=?";
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
                $success = "Diagnosis Deleted Successfully";
            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}

		if(isset($_POST['diagnosis']))
		{
            $name=strtoupper($_POST['name']);
             $amount=$_POST['amount'];
            

            //$dir="productimages";
//unlink($dir.'/'.$pimage);

            //sql to insert captured values
            $query="insert into diagnosis(name,amount) values(?,?)";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('ss',$name,$amount);
            $stmt->execute();
            /*
            *Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
            *echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
            */ 
            //declare a varible which will be passed to alert function
            if($stmt)
            {
                $success = "Diagnosis Registered Successfully";
            }
            else {
                $err = "Please Try Again Or Try Later";
            }
			
			
		}
        elseif(isset($_POST['updatediagnosis']))
        {
            $cname=$_POST['dname'];
             $camount=$_POST['damount'];
            

            //$dir="productimages";
//unlink($dir.'/'.$pimage);

            //sql to insert captured values
            $query="update diagnosis set amount=? where name=?";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('ss',$camount,$cname);
            $stmt->execute();
            /*
            *Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
            *echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
            */ 
            //declare a varible which will be passed to alert function
            if($stmt)
            {
                $success = "Diagnosis Updated Successfully";
            }
            else {
                $err = "Please Try Again Or Try Later";
            }
            
            
        }



function getdiagnosiss(){
  
$sql="SELECT * FROM scan ORDER BY id DESC "; 
$stmt= $mysqli->prepare($sql) ;
    $stmt->execute() ;
    $res=$stmt->get_result();
$cnt=1;
while($reply=$res->fetch_object()){
                                                            
 echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";

      } 
    
}
function getdiagnosis($mysqli){
    $sql = "SELECT * FROM diagnosis ORDER BY id ASC";
    $result = mysqli_query($mysqli,$sql);
    while($reply = mysqli_fetch_array($result)){
        echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";
    }
}

//procedures
if(isset($_GET['delp'])){

     //sql to insert captured values
    $id=$_GET['delp'];
            $query="delete from procedures where id=?";
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
                $success = "Procedures Deleted Successfully";
            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}

        if(isset($_POST['procedures']))
        {
            $name=strtoupper($_POST['namep']);
             $amount=$_POST['amountp'];
            

            //$dir="productimages";
//unlink($dir.'/'.$pimage);

            //sql to insert captured values
            $query="insert into procedures(name,amount) values(?,?)";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('ss',$name,$amount);
            $stmt->execute();
            /*
            *Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
            *echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
            */ 
            //declare a varible which will be passed to alert function
            if($stmt)
            {
                $success = "Procedures Registered Successfully";
            }
            log_action($_SESSION['user_id'], "Registered Procedures: $name"); // ✅ Add log entry
            else {
                $err = "Please Try Again Or Try Later";
            }
            
            
        }
        elseif(isset($_POST['updateprocedures']))
        {
            $cname=$_POST['pname'];
             $camount=$_POST['pamount'];
            

            //$dir="productimages";
//unlink($dir.'/'.$pimage);

            //sql to insert captured values
            $query="update procedures set amount=? where name=?";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('ss',$camount,$cname);
            $stmt->execute();
            /*
            *Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
            *echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
            */ 
            //declare a varible which will be passed to alert function
            if($stmt)
            {
                $success = "Procedures Updated Successfully";
            }
            log_action($_SESSION['user_id'], "Update procedures: $name"); // ✅ Add log entry
            else {
                $err = "Please Try Again Or Try Later";
            }
            
            
        }



function getproceduress(){
  
$sql="SELECT * FROM procedures ORDER BY id DESC "; 
$stmt= $mysqli->prepare($sql) ;
    $stmt->execute() ;
    $res=$stmt->get_result();
$cnt=1;
while($reply=$res->fetch_object()){
                                                            
 echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";

      } 
    
}
function getprocedures($mysqli){
    $sql = "SELECT * FROM procedures ORDER BY id ASC";
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
                                    <h4 class="page-title">Hospital Diagnosis & Procedures Registration Form</h4>
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
                                                    <label for="inputCity" class="col-form-label"><h3>Diagnosis Name</h3></label>
                                                    <input required="required" type="text" style="color:blue; font-size:medium;"  name="name"placeholder="Enter Diagnosis Name" disable class="form-control" id="inputCity">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Diagnosis Amount</h3></label>
                                                    <input type="text" style="color:red;font-size:medium;" required="required" name="amount" class="form-control" id="inputEmail4" placeholder="Enter Amount">
                                                </div>
                                                
                                            </div>
                                            
                                            


                                            <button type="submit" name="diagnosis" class="ladda-button btn btn-primary" data-style="expand-right">Register Diagnosis</button>

                                        </form>
<hr>

                                    <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                           
                                            <div class="form-row">
                                                
                                                <div class="form-group col-md-4">
                                                    <label for="inputState" class="col-form-label"><h3>Select Diagnosis Name</h3></label>
                                                    <select id="inputState" required="required" name="dname" class="form-control">
                                                        <option>Choose</option>
                                                       <?php getdiagnosis($mysqli); ?>
                                                    </select>
                                                </div>

                                                 <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Updated Amount</h3></label>
                                                    <input type="text" style="color:red;font-size:medium;" required="required" name="damount" class="form-control" id="inputEmail4" placeholder="Enter Updated Amount">
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
                                                <button type="submit" name="updatediagnosis" class="ladda-button btn btn-primary" data-style="expand-right">Update Diagnosis Amount</button>
                                        </form>
                                        <!--End Patient Form-->

<div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h4 class="header-title">List of Diagnosis</h4>
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
                                              
                                                <th data-hide="phone" style="color:white;">Diagnosis Name</th>
                                                <th data-hide="phone" style="color:white;">Diagnosis Amount</th>
                                                
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM  diagnosis ORDER BY id ASC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $row->id;?></td>
                                                    <td><?php echo $row->name;?></td>
                                                    <td><?php echo $row->amount;?></td>
                                                    

                                                    <td><a href="diagnosis_procedures.php.php?code=<?php echo $row->id;?>" class=""><img src="assets/images/ok.png" height="20" width="20"></a><a href="diagnosis_procedures.php.php?del=<?php echo $row->id;?>" class=""><img src="assets/images/del.png" height="20" width="20"></a></td>
                                                </tr>
                                                </tbody>
                                            <?php  $cnt = $cnt +1; }?>
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

 <!--Add Patient Form-->
                                        <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                             <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Procedures Name</h3></label>
                                                    <input required="required" type="text" style="color:blue; font-size:medium;"  name="namep"placeholder="Enter Procedures Name" disable class="form-control" id="inputCity">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Procedures Amount</h3></label>
                                                    <input type="text" style="color:red;font-size:medium;" required="required" name="amountp" class="form-control" id="inputEmail4" placeholder="Enter Amount">
                                                </div>
                                                
                                            </div>
                                            
                                            


                                            <button type="submit" name="procedures" class="ladda-button btn btn-primary" data-style="expand-right">Register Procedures</button>

                                        </form>
<hr>

                                    <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                           
                                            <div class="form-row">
                                                
                                                <div class="form-group col-md-4">
                                                    <label for="inputState" class="col-form-label"><h3>Select Procedures Name</h3></label>
                                                    <select id="inputState" required="required" name="pname" class="form-control">
                                                        <option>Choose</option>
                                                       <?php getprocedures($mysqli); ?>
                                                    </select>
                                                </div>

                                                 <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Updated Amount</h3></label>
                                                    <input type="text" style="color:red;font-size:medium;" required="required" name="pamount" class="form-control" id="inputEmail4" placeholder="Enter Updated Amount">
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
                                                <button type="submit" name="updateprocedures" class="ladda-button btn btn-primary" data-style="expand-right">Update Procedures Amount</button>
                                        </form>
                                        <!--End Patient Form-->

<div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h4 class="header-title">List of Procedures</h4>
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
                                              
                                                <th data-hide="phone" style="color:white;">Procedures Name</th>
                                                <th data-hide="phone" style="color:white;">Procedures Amount</th>
                                                
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM  procedures ORDER BY id ASC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $row->id;?></td>
                                                    <td><?php echo $row->name;?></td>
                                                    <td><?php echo $row->amount;?></td>
                                                    

                                                    <td><a href="diagnosis_procedures.php?code=<?php echo $row->id;?>" class=""><img src="assets/images/ok.png" height="20" width="20"></a><a href="diagnosis_procedures?delp=<?php echo $row->id;?>" class=""><img src="assets/images/del.png" height="20" width="20"></a></td>
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