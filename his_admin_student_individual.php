<!--Server side code to handle  Patient Registration-->
<?php
    session_start();
    include('assets/inc/config.php');
    
    // Helper: fetch student records from central UG portal API
    function fetch_ug_students($page = 1, $pageSize = 50, $username = 'deicon', $password = 'deicon', $regnum = null)
    {
        $baseUrl = 'https://portal.oouagoiwoye.edu.ng/api/get_users.php';

        $params = array(
            'data'      => 'UG',
            'page'      => (int) $page,
            'page_size' => (int) $pageSize,
            'username'  => $username,
            'password'  => $password,
        );

        if (!empty($regnum)) {
            $params['regnum'] = $regnum;
        }

        $url = $baseUrl . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 20,
        ));

        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return array();
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return array();
        }

        return $data;
    }
        if(isset($_POST['add_patient']))
        {
            $pat_stcode=$_POST['pat_stcode'];
            $pat_title=$_POST['title'];
            $pat_surn=$_POST['surn'];
            $pat_fname=$_POST['fname'];
            $pat_mname=$_POST['mname'];
            $pat_matric=$_POST['pat_matric'];
            $pat_dept=$_POST['dept'];
            $pat_faculty=$_POST['faculty'];
            $date=$_POST['regdate'];
            $pat_dob=$_POST['dob'];
            $pat_age=$_POST['age'];
            $pat_addr=$_POST['add'];
            $pat_phone=$_POST['phone'];
            $nok=$_POST['nok'];
            $noknumber=$_POST['noknumber'];
             $mstatus=$_POST['mstatus'];
            $pics=$_FILES["pics"]["name"];
            
            
//$dir="productimages";
//unlink($dir.'/'.$pimage);


                 move_uploaded_file($_FILES["pics"]["tmp_name"],"picture/".$_FILES["pics"]["name"]);
   
            $query="insert into student(stcode,title,surname,firstname,middlename,matric_no,dept,faculty,reg_date,dob,age,address,phone,nok,nok_contact,marital_status,picture) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('sssssssssssssssss',$pat_stcode, $pat_title,  $pat_surn, $pat_fname,  $pat_mname, $pat_matric, $pat_dept, $pat_faculty, $date, $pat_dob, $pat_age, $pat_addr, $pat_phone, $nok, $noknumber, $mstatus, $pics);
            $stmt->execute();
            /*
            *Use Sweet Alerts Instead Of This Fucked Up Javascript Alerts
            *echo"<script>alert('Successfully Created Account Proceed To Log In ');</script>";
            */ 
            //declare a varible which will be passed to alert function
            if($stmt)
            {
                $success = "Student Registration Successful";
            }
            else {
            $err = "Please Try Again Or Try Later";
            }
            
            
        }


        $ind="ST"."4".rand(0,7729); 
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
                                    <h4 class="page-title">Student Card Form</h4>
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

                                             <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Student Code</h3></label>
                                                    <input required="required" type="text" style="color:blue;" value="<?php echo $ind;  ?>" name="pat_stcode" class="form-control" id="inputCity">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Student Matric Number</h3></label>
                                                    <input required="required" type="text" style="color:blue;" name="pat_matric" class="form-control" id="inputCity" placeholder="Matric no">
                                                </div>
                                               
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Registration Date</h3></label>
                                                    <input type="date" required="required" name="regdate" class="form-control" id="inputEmail4" placeholder="DD/MM/YYYY">
                                                </div>
                                                
                                            </div>
                                            <div class="form-row">
                                                 <div class="form-group col-md-4">
                                                    <label for="inputState" class="col-form-label">Title</label>
                                                    <select id="inputState" required="required" name="title" class="form-control">
                                                        <option>Choose</option>
                                                         <option value="Mr">Mr</option>
                                                        <option value="Miss">Miss</option>
                                                        <option value="Mrs">Mrs</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label">Surname</label>
                                                    <input type="text" required="required" name="surn" class="form-control" id="inputEmail4" placeholder="Patient's Surname ">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label">First Name</label>
                                                    <input type="text" required="required" name="fname" class="form-control" id="inputEmail4" placeholder="Patient's First Name">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">Middle Name</label>
                                                    <input required="required" type="text" name="mname" class="form-control"  id="inputPassword4" placeholder="Patient`s Middle Name">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">Department</label>
                                                    <input required="required" type="text" name="dept" class="form-control"  id="inputPassword4" placeholder="Department">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">Faculty</label>
                                                    <input required="required" type="text" name="faculty" class="form-control"  id="inputPassword4" placeholder="Faculty">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="inputEmail4" class="col-form-label">Date Of Birth</label>
                                                    <input type="date" required="required" name="dob" class="form-control" id="inputEmail4" placeholder="DD/MM/YYYY">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="inputPassword4" class="col-form-label">Age</label>
                                                    <input required="required" type="text" name="age" class="form-control"  id="inputPassword4" placeholder="Patient`s Age">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="inputAddress" class="col-form-label">Address</label>
                                                <input required="required" type="text" class="form-control" name="add" id="inputAddress" placeholder="Patient's Addresss">
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Mobile Number</label>
                                                    <input required="required" type="text" name="phone" class="form-control" id="inputCity">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Patient NOK</label>
                                                    <input required="required" type="text" name="nok" class="form-control" id="inputCity">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">NOK Mobile Number</label>
                                                    <input required="required" type="text" name="noknumber" class="form-control" id="inputCity">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="inputState" class="col-form-label">Marital Status</label>
                                                    <select id="inputState" required="required" name="mstatus" class="form-control">
                                                        <option>Choose</option>
                                                         <option value="Single">Single</option>
                                                        <option value="Married">Married</option>
                                                        <option value="Divorce">Divorce</option>
                                                    </select>
                                                </div>
                                                 <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="inputState" class="col-form-label">Profile Picture</label>
                                                                <input type="file" name="pics" class="form-control btn btn-success" id="useremail" onChange="husbandURL(this);"  >
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                         <div class="form-group">
                                                                <label for="inputState" class="col-form-label">Uploaded Picture</label>
                                                               <img alt="" class=" img-thumbnail pull-right" id="hpass" src="#" width="300" height="220"  />                                                            </div>
                                                </div>
                                                <div class="col-md-4">
                                                         <div class="form-group">
                                                                <label for="inputState" class="col-form-label">Webcam Picture</label>
                                                                <video id="video" width="300" height="220" style="background-color:grey;"  autoplay></video><p>
                                                                 <button id="snap" class="ladda-button btn btn-primary">Take Picture</button>
                                                            </div>
                                                </div>
                                                <div class="col-md-4">
                                                         <div class="form-group">
                                                                <label for="inputState" class="col-form-label">Captured Image</label><p>
                                                               <canvas id="canvas"  width="300" height="220" style="background-color:grey;"></canvas>                                                            </div>
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

                                            <button type="submit" name="add_patient" class="ladda-button btn btn-primary" data-style="expand-right">Add Patient</button>

                                        </form>
                                        <!--End Patient Form-->


<div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h4 class="header-title">List of Students </h4>
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

                                                <th data-hide="phone" style="color:white;">StudentId</th>
                                                <th data-hide="phone" style="color:white;">Matricno</th>
                                                <th data-hide="phone" style="color:white;">Registration</th>
                                                <th data-hide="phone" style="color:white;">Title</th>
                                                <th data-hide="phone" style="color:white;">Surname</th>
                                            // Prefer live data from central UG portal; fall back to local table on failure
                                            $students = fetch_ug_students(1, 100);
                                            $cnt = 1;

                                            if (!empty($students)) {
                                                foreach ($students as $stu) {
                                                    $regnum   = isset($stu['regnum']) ? $stu['regnum'] : '';
                                                    $surname  = isset($stu['sname']) ? $stu['sname'] : '';
                                                    $fname    = isset($stu['fname']) ? $stu['fname'] : '';
                                                    $mname    = isset($stu['mname']) ? $stu['mname'] : '';
                                                    $dept     = isset($stu['dept']) ? $stu['dept'] : '';
                                                    $faculty  = isset($stu['faculty']) ? $stu['faculty'] : '';
                                                    $dobStr   = isset($stu['dob']) ? $stu['dob'] : '';
                                                    $phone    = isset($stu['tel']) ? $stu['tel'] : '';
                                                    $address  = isset($stu['ad']) ? $stu['ad'] : '';
                                                    $sex      = isset($stu['sex']) ? strtoupper($stu['sex']) : '';

                                                    // Infer simple title from sex where possible
                                                    $title = '';
                                                    if ($sex === 'MALE') {
                                                        $title = 'Mr';
                                                    } elseif ($sex === 'FEMALE') {
                                                        $title = 'Miss';
                                                    }

                                                    // Compute age from DOB in format dd/mm/yyyy if available
                                                    $age = '';
                                                    if (!empty($dobStr)) {
                                                        $dobParts = explode('/', $dobStr);
                                                        if (count($dobParts) === 3) {
                                                            $d = (int) $dobParts[0];
                                                            $m = (int) $dobParts[1];
                                                            $y = (int) $dobParts[2];
                                                            if ($y > 0 && $m > 0 && $d > 0) {
                                                                $dob = DateTime::createFromFormat('d/m/Y', $dobStr);
                                                                if ($dob instanceof DateTime) {
                                                                    $now  = new DateTime();
                                                                    $diff = $now->diff($dob);
                                                                    $age  = $diff->y;
                                                                }
                                                            }
                                                        }
                                                    }

                                            ?>
                                            </thead>
                                            <?php
                                            /*
                                                    <td><?php echo $cnt;?></td>
                                                    <td><?php echo htmlentities($regnum);?></td>
                                                    <td><?php echo htmlentities($regnum);?></td>
                                                    <td></td>
                                                    <td><?php echo htmlentities($title);?></td>
                                                    <td><?php echo htmlentities($surname);?></td>
                                                    <td><?php echo htmlentities($fname);?></td>
                                                    <td><?php echo htmlentities($mname);?></td>
                                                    <td><?php echo htmlentities($dept);?></td>
                                                    <td><?php echo htmlentities($faculty);?></td>
                                                    <td><?php echo htmlentities($age);?></td>
                                                    <td></td>
                                                    <td><?php echo htmlentities($dobStr);?></td>
                                                    <td><?php echo htmlentities($phone);?></td>
                                                    <td><?php echo htmlentities($address);?></td>
                                                    <td><a href="his_admin_student_individual.php?code=<?php echo urlencode($regnum);?>" class="badge badge-success"><i class="far fa-eye "></i> View</a></td>
                                                    <td><?php echo $row->reg_date;?></td>
                                                    <td><?php echo $row->title;?></td>
                                            <?php  $cnt = $cnt +1 ; }
                                            } else {
                                                // Fallback to local student table if API is unavailable
                                                $ret = "SELECT * FROM  student ORDER BY id DESC ";
                                                $stmt = $mysqli->prepare($ret);
                                                $stmt->execute();
                                                $res = $stmt->get_result();
                                                $cnt = 1;
                                                while ($row = $res->fetch_object()) {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $row->id;?></td>
                                                    <td><?php echo $row->stcode;?></td>
                                                    <td><?php echo $row->matric_no;?></td>
                                                    <td><?php echo $row->reg_date;?></td>
                                                    <td><?php echo $row->title;?></td>
                                                    <td><?php echo $row->surname;?></td>
                                                    <td><?php echo $row->firstname;?></td>
                                                    <td><?php echo $row->middlename;?></td>
                                                    <td><?php echo $row->dept;?></td>
                                                    <td><?php echo $row->faculty;?></td>
                                                    <td><?php echo $row->age;?></td>
                                                    <td><?php echo $row->marital_status;?></td>
                                                    <td><?php echo $row->reg_date;?></td>
                                                    <td><?php echo $row->phone;?></td>
                                                    <td><?php echo $row->address;?></td>
                                                    <td><a href="his_admin_student_individual.php?code=<?php echo $row->stcode;?>" class="badge badge-success"><i class="far fa-eye "></i> View</a></td>
                                                </tr>
                                                </tbody>
                                            <?php $cnt = $cnt + 1; }
                                            }
                                            ?>
                                            <tfoot>
                                                    <td><?php echo $row->middlename;?></td>
                                                    <td><?php echo $row->dept;?></td>
                                                    <td><?php echo $row->faculty;?></td>
                                                    <td><?php echo $row->age;?></td>
                                                    <td><?php echo $row->marital_status;?></td>
                                                    <td><?php echo $row->reg_date;?></td>
                                                    <td><?php echo $row->phone;?></td>
                                                    <td><?php echo $row->address;?></td
                                                    
                                                    <td><a href="his_admin_student_individual.php?code=<?php echo $row->stcode;?>" class="badge badge-success"><i class="far fa-eye "></i> View</a></td>
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