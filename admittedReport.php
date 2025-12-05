<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
    $ind=$_GET['id'];
    $df=$_GET['df'];
    $dt=$_GET['dt'];

if (strpos($ind, 'IND') !== false) {

    $ret="SELECT * FROM individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $row=$res->fetch_object();
    $surn=$row->surname;
    $firstname=$row->firstname;
    $lname=$row->lastname;
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
    $surn=$row->Surname;
    $firstname=$row->Firstname;
    $lname=$row->Lastname;
     $cate="FAMILY CARD";
    $phone=$row->phone;
    $nok=$row->nok;
    $nokph=$row->nok_phone;
    $dob=$row->dob;
    $date=$row->reg_date;
    $pic=$row->picture;
}
elseif (strpos($ind, 'C') !== false) {
    $ret="SELECT * FROM company_individual where code='$ind'"; 
    $stmt= $mysqli->prepare($ret) ;
    $stmt->execute() ;//ok
    $res=$stmt->get_result();
    $cnt=1;
    $row=$res->fetch_object();
    $surn=$row->Surname;
    $firstname=$row->Firstname;
     $cate="COMPANY CARD";
    $lname=$row->Lastname;
    $phone=$row->phone;
    $nok=$row->nok;
    $nokph=$row->nok_phone;
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
    $surn=$row->Surname;
    $firstname=$row->Firstname;
    $lname=$row->Lastname;
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
    $lname=$row->lastname;
    $phone=$row->phone;
    $nok=$row->nok;
    $nokph=$row->nok_contact;
    $dob=$row->dob;
    $date=$row->reg_date;
    $pic=$row->picture;
}else{}




		


       
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
            <?php include('assets/inc/slidebar_nur.php');?>
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
                                    <h4 class="page-title">Nursing Admittion Report</h4>
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
                                                <div class="form-group col-md-3">
                                                    <label for="inputCity" class="col-form-label"><h3>Patient Code</h3></label>
                                                    <input required="required" type="text" style="color:blue; font-size:x-large; background-color:grey;" name="patcode" class="form-control" id="inputCity" value="<?php echo $ind; ?>">
                                                </div>
                                               <div class="form-group col-md-5">
                                                    <label for="inputCity" class="col-form-label"><h3>Patient Fullname</h3></label>
                                                    <input required="required" type="text" style="color:blue; font-size:x-large; background-color:grey;" name="name" class="form-control" id="inputCity" value="<?php $fname=$surn." ".$firstname." ".$lname; echo $fname; ?>">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Card Type</h3></label>
                                                        <input required="required" type="text" style="color:blue; font-size:x-large; background-color:grey;" name="name" class="form-control" id="inputCity" value="<?php echo $cate; ?>">
                                                </div>
                                                
                                            </div>
                                            
                                           


                                        </form>


                                           

                                        <!--End Patient Form-->


<div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h2 class="header-title"><h3>Patient Drug Analysis</h3> </h2>
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

                                                <th data-hide="phone" style="color:white;">Patient Id</th>
                                                <th data-hide="phone" style="color:white;">Fullname</th>
                                               
                                                <th data-hide="phone" style="color:white;">Session</th>
                                                <th data-hide="phone" style="color:white;">Drug</th>
                                                <th data-hide="phone" style="color:white;">Dose</th>
                                                 <th data-hide="phone" style="color:white;">Time</th>
                                                <th data-hide="phone" style="color:white;">Comment</th>
                                                
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of daily posting
                                                *
                                            */
                                            $dated=date('Y-m-d');
                                                $ret="SELECT * FROM  drugchart where patid='$ind' and  date between '$df' and '$dt' ORDER BY id DESC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                
                                                    <td><?php echo $row->patid;?></td>
                                                    <td><?php echo $row->Fullname;?></td>
                                                    <td><?php echo $row->session;?></td>
                                                     <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->drug;?></td>
                                                    <td><?php echo $row->dose;?></td>
                                                    <td><?php echo $row->time;?></td>
                                                    <td><?php echo $row->comment;?> </td>

                                                   
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



<!--End Patient Form-->


<div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h2 class="header-title"><h3>Patient Injection Analysis</h3> </h2>
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

                                                <th data-hide="phone" style="color:white;">Patient Id</th>
                                                <th data-hide="phone" style="color:white;">Fullname</th>
                                               
                                                <th data-hide="phone" style="color:white;">Session</th>
                                                <th data-hide="phone" style="color:white;">Drug</th>
                                                <th data-hide="phone" style="color:white;">Dose</th>
                                                 <th data-hide="phone" style="color:white;">Time</th>
                                                <th data-hide="phone" style="color:white;">Comment</th>
                                                
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of daily posting
                                                *
                                            */
                                            $dated=date('Y-m-d');
                                                $ret="SELECT * FROM  injectionchart where patid='$ind' and  date between '$df' and '$dt' ORDER BY id DESC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                
                                                    <td><?php echo $row->patid;?></td>
                                                    <td><?php echo $row->Fullname;?></td>
                                                    <td><?php echo $row->session;?></td>
                                                     <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->drug;?></td>
                                                    <td><?php echo $row->dose;?></td>
                                                    <td><?php echo $row->time;?></td>
                                                    <td><?php echo $row->comment;?> </td>

                                                   
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

 function husbandURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        $('#hpass').attr('src', e.target.result);
       }
        reader.readAsDataURL(input.files[0]);
       }
    }

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