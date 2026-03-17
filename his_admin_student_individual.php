<?php
// Server side code to handle Patient Registration
    session_start();
    include('assets/inc/config.php');

    // Simple status messages for the UI
    $err = '';
    $success = '';

    // Helper: fetch student records via local OOU proxy API
    // Note: $username and $password are no longer used; kept for compatibility.
    function fetch_ug_students($page = 1, $pageSize = 50, $username = null, $password = null, $regnum = null)
    {
        // Build base URL to local proxy (oou_student_api.php in web root)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
        if ($dir === '') {
            $dir = '/';
        }
        $baseUrl = $scheme . $host . $dir . '/oou_student_api.php';

        $params = array(
            'type'  => 'UG',
            'page'  => (int) $page,
            'limit' => (int) $pageSize,
        );

        if (!empty($regnum)) {
            $params['regnum'] = $regnum;
        }

        $url = $baseUrl . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => array(
                'Authorization: my_secure_hospital_key',
            ),
        ));

        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return array();
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['status']) || $data['status'] !== 'success' || !isset($data['data']) || !is_array($data['data'])) {
            return array();
        }

        // Return only the student list portion
        return $data['data'];
    }

    // Defaults for pre-filled form values (from UG API)
    $pref_matric    = '';
    $pref_title     = '';
    $pref_surn      = '';
    $pref_fname     = '';
    $pref_mname     = '';
    $pref_dept      = '';
    $pref_faculty   = '';
    $pref_dob       = '';
    $pref_age       = '';
    $pref_addr      = '';
    $pref_phone     = '';
    $pref_nok       = '';
    $pref_noknumber = '';
    $pref_passport  = '';

    // If a matric number is supplied via GET, fetch details from UG portal
    $lookup_matric = isset($_GET['lookup_matric']) ? trim($_GET['lookup_matric']) : '';
    if ($lookup_matric !== '') {
        $students = fetch_ug_students(1, 50, 'deicon', 'deicon', $lookup_matric);
        if (!empty($students)) {
            // Prefer an exact regnum match to the matric entered
            $stu = null;
            foreach ($students as $candidate) {
                if (isset($candidate['matric_no']) && strcasecmp(trim($candidate['matric_no']), trim($lookup_matric)) === 0) {
                    $stu = $candidate;
                    break;
                }
            }

            if ($stu !== null) {
                $pref_matric  = isset($stu['matric_no']) ? $stu['matric_no'] : $lookup_matric;
                $pref_surn    = isset($stu['surname']) ? $stu['surname'] : '';
                $pref_fname   = isset($stu['first_name']) ? $stu['first_name'] : '';
                $pref_mname   = isset($stu['middle_name']) ? $stu['middle_name'] : '';
                $pref_dept    = isset($stu['department']) ? $stu['department'] : '';
                $pref_faculty = isset($stu['faculty']) ? $stu['faculty'] : '';
                $pref_addr    = isset($stu['address']) ? $stu['address'] : '';
                $pref_phone   = isset($stu['phone']) ? $stu['phone'] : '';
                $pref_nok     = isset($stu['nok']) ? $stu['nok'] : '';
                $pref_noknumber = isset($stu['nok_phone']) ? $stu['nok_phone'] : '';

                // Passport URL from UG portal (if provided by API)
                if (!empty($stu['passport_url'])) {
                    $pref_passport = $stu['passport_url'];
                } elseif (!empty($stu['pass']) && !empty($pref_matric)) {
                    // Fallback guess: base URL + matric number pattern, if needed in future
                    $pref_passport = rtrim($stu['pass'], '/') . '/passports/' . preg_replace('/[^A-Z0-9]/i', '', $pref_matric) . '.jpg';
                }

                // Infer title from sex
                $sex = isset($stu['sex']) ? strtoupper($stu['sex']) : '';
                if ($sex === 'MALE') {
                    $pref_title = 'Mr';
                } elseif ($sex === 'FEMALE') {
                    $pref_title = 'Miss';
                }

                // DOB is in format dd/mm/yyyy from API; convert to yyyy-mm-dd for HTML date
                if (!empty($stu['dob'])) {
                    $dob = DateTime::createFromFormat('d/m/Y', $stu['dob']);
                    if ($dob instanceof DateTime) {
                        $pref_dob = $dob->format('Y-m-d');
                        $now  = new DateTime();
                        $diff = $now->diff($dob);
                        $pref_age = $diff->y;
                    }
                }
            } else {
                // No exact match was found in the API response
                $pref_matric = $lookup_matric;
                $err = 'No UG record found for matric ' . htmlspecialchars($lookup_matric);
            }
        } else {
            $pref_matric = $lookup_matric;
            $err = 'No UG record found for matric ' . htmlspecialchars($lookup_matric);
        }
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

            // Ensure department/faculty strings fit DB column sizes (dept/faculty are VARCHAR(20))
            $pat_dept = substr($pat_dept, 0, 20);
            $pat_faculty = substr($pat_faculty, 0, 20);
            
            
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

                                        <?php if(!empty($success)) { ?>
                                            <div class="alert alert-success" role="alert">
                                                <?php echo htmlspecialchars($success); ?>
                                            </div>
                                        <?php } ?>

                                        <?php if(!empty($err)) { ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?php echo htmlspecialchars($err); ?>
                                            </div>
                                        <?php } ?>

                                        <!-- Lookup from UG portal by matric number -->
                                        <form method="get" action="his_admin_student_individual.php" class="form-inline mb-3">
                                            <div class="form-group col-md-4">
                                                <label for="lookupMatric" class="col-form-label">Fetch From UG Portal (Matric No)</label>
                                                <input type="text" name="lookup_matric" id="lookupMatric" class="form-control" style="color:blue;" value="<?php echo htmlspecialchars($lookup_matric); ?>" placeholder="Enter Matric No and click Fetch">
                                            </div>
                                            <div class="form-group col-md-2" style="margin-top:32px;">
                                                <button type="submit" class="btn btn-secondary">Fetch</button>
                                            </div>
                                        </form>

                                        <!--Add Patient Form-->
                                        <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" >
                                             <div class="form-row">

                                             <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Student Code</h3></label>
                                                    <input required="required" type="text" style="color:blue;" value="<?php echo $ind;  ?>" name="pat_stcode" class="form-control" id="inputCity">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Student Matric Number</h3></label>
                                                    <input required="required" type="text" style="color:blue;" name="pat_matric" class="form-control" id="inputCity" placeholder="Matric no" value="<?php echo htmlspecialchars($pref_matric); ?>">
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
                                                        <option value="">Choose</option>
                                                        <option value="Mr" <?php if($pref_title=='Mr') echo 'selected'; ?>>Mr</option>
                                                        <option value="Miss" <?php if($pref_title=='Miss') echo 'selected'; ?>>Miss</option>
                                                        <option value="Mrs" <?php if($pref_title=='Mrs') echo 'selected'; ?>>Mrs</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label">Surname</label>
                                                    <input type="text" required="required" name="surn" class="form-control" id="inputEmail4" placeholder="Patient's Surname " value="<?php echo htmlspecialchars($pref_surn); ?>">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label">First Name</label>
                                                    <input type="text" required="required" name="fname" class="form-control" id="inputEmail4" placeholder="Patient's First Name" value="<?php echo htmlspecialchars($pref_fname); ?>">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">Middle Name</label>
                                                    <input required="required" type="text" name="mname" class="form-control"  id="inputPassword4" placeholder="Patient`s Middle Name" value="<?php echo htmlspecialchars($pref_mname); ?>">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">Department</label>
                                                    <input required="required" type="text" name="dept" class="form-control"  id="inputPassword4" placeholder="Department" value="<?php echo htmlspecialchars($pref_dept); ?>">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">Faculty</label>
                                                    <input required="required" type="text" name="faculty" class="form-control"  id="inputPassword4" placeholder="Faculty" value="<?php echo htmlspecialchars($pref_faculty); ?>">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="inputEmail4" class="col-form-label">Date Of Birth</label>
                                                    <input type="date" required="required" name="dob" class="form-control" id="inputEmail4" placeholder="DD/MM/YYYY" value="<?php echo htmlspecialchars($pref_dob); ?>">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="inputPassword4" class="col-form-label">Age</label>
                                                    <input required="required" type="text" name="age" class="form-control"  id="inputPassword4" placeholder="Patient`s Age" value="<?php echo htmlspecialchars($pref_age); ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="inputAddress" class="col-form-label">Address</label>
                                                <input required="required" type="text" class="form-control" name="add" id="inputAddress" placeholder="Patient's Addresss" value="<?php echo htmlspecialchars($pref_addr); ?>">
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Mobile Number</label>
                                                    <input required="required" type="text" name="phone" class="form-control" id="inputCity" value="<?php echo htmlspecialchars($pref_phone); ?>">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">Patient NOK</label>
                                                    <input required="required" type="text" name="nok" class="form-control" id="inputCity" value="<?php echo htmlspecialchars($pref_nok); ?>">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label">NOK Mobile Number</label>
                                                    <input required="required" type="text" name="noknumber" class="form-control" id="inputCity" value="<?php echo htmlspecialchars($pref_noknumber); ?>">
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
                                                                <label for="inputState" class="col-form-label">Uploaded / Portal Picture</label>
                                                               <img alt="Student passport" class=" img-thumbnail pull-right" id="hpass" src="<?php echo $pref_passport ? htmlspecialchars($pref_passport) : '#'; ?>" width="300" height="220"  />                                                            </div>
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
                                                <th data-hide="phone" style="color:white;">Firstname</th>
                                                <th data-hide="phone" style="color:white;">Middlename</th>
                                                <th data-hide="phone" style="color:white;">Department</th>
                                                <th data-hide="phone" style="color:white;">Faculty</th>
                                                <th data-hide="phone" style="color:white;">Age</th>
                                                <th data-hide="phone" style="color:white;">MStatus</th>
                                                <th data-hide="phone" style="color:white;">DOB</th>
                                                <th data-hide="phone" style="color:white;">Phone</th>
                                                <th data-hide="phone" style="color:white;">Address</th>
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM  student ORDER BY id DESC "; 
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