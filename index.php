<?php
    session_start();
    include('assets/inc/config.php');
    include('assets/inc/functions.php');
    if(isset($_POST['admin_login']))
    {
        $date=date('Y-m-d');
        pharmacyopeningstock($date,$mysqli);
        storeopeningstock($date,$mysqli);

        $campusname=$_POST['location'];
         $doc_number = $_POST['ad_id'];
         $st='ACTIVE';
        //$doc_email = $_POST['doc_ea']
         //$doc_dept='Records';
        $doc_pwd = sha1(md5($_POST['ad_pwd']));//double encrypt to increase security
        $stmt=$mysqli->prepare("SELECT doc_number, doc_pwd, doc_id, doc_dept FROM his_docs WHERE  doc_number=? AND doc_pwd=? and status=?");//sql to log in user
        $stmt->bind_param('sss', $doc_number, $doc_pwd,$st);//bind fetched parameters
        $stmt->execute();//execute bind
        $stmt -> bind_result($doc_number, $doc_pwd, $doc_id, $doc_dept);//bind result
        $rs=$stmt->fetch();
        $stmt->close();
        $campid = getcampusid($campusname,$mysqli);
        // If the user selected a valid campus at login, store the numeric id and use it as working location.
        if (is_numeric($campid) && $campid) {
            $_SESSION['campus_id'] = (int) $campid;
            $_SESSION['working_location_id'] = (int) $campid;
            // also store the human readable name if available
            $lstmt = $mysqli->prepare("SELECT name FROM campus_locations WHERE id = ? LIMIT 1");
            if ($lstmt) {
                $lstmt->bind_param('i', $campid);
                $lstmt->execute();
                $lres = $lstmt->get_result();
                if ($lrow = $lres->fetch_assoc()) {
                    $_SESSION['working_location'] = $lrow['name'];
                }
            }
        } else {
            // fallback: keep campus session null
            $_SESSION['campus_id'] = null;
        }
        $_SESSION['doc_id'] = $doc_id;
        $_SESSION['doc_number'] = $doc_number;//Assign session to doc_number id
        //$uip=$_SERVER['REMOTE_ADDR'];
        //$ldate=date('d/m/Y h:i:s', time());
        // Attach campus/location to session if available
        $campus_id = null;
        $col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='his_docs' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
        if ($col_exists) {
            $cstmt = $mysqli->prepare("SELECT campus_id FROM his_docs WHERE doc_id = ? LIMIT 1");
            if ($cstmt) {
                $cstmt->bind_param('i', $doc_id);
                $cstmt->execute();
                $cres = $cstmt->get_result();
                if ($crow = $cres->fetch_assoc()) {
                    $campus_id = $crow['campus_id'];
                    if (!empty($campus_id)) {
                        // Only set working location from his_docs when it hasn't been set by the login campus selection
                        if (!isset($_SESSION['working_location_id']) || empty($_SESSION['working_location_id'])) {
                            $_SESSION['working_location_id'] = (int) $campus_id;
                            $lstmt = $mysqli->prepare("SELECT name FROM campus_locations WHERE id = ? LIMIT 1");
                            if ($lstmt) {
                                $lstmt->bind_param('i', $campus_id);
                                $lstmt->execute();
                                $lres = $lstmt->get_result();
                                if ($lrow = $lres->fetch_assoc()) {
                                    $_SESSION['working_location'] = $lrow['name'];
                                }
                            }
                        }
                    }
                }
            }
        }
        if($rs)
            {//if its sucessfull

                if($doc_dept=='Records'){
                    log_action($doc_id,"LOGIN");
                    header("location:record_dashboard.php?$campid");
                    }
                    else if($doc_dept=='Nursing')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:nursing_dashboard.php?$campid");

                    }
                    else if($doc_dept=='Administrator')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:admin_dashboard.php?$campid");

                    }
                    else if($doc_dept=='Cashier')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:cashier_dashboard.php");

                    }
                    else if($doc_dept=='Pharmacy')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:pharmacy_dashboard.php");

                    }
                         else if($doc_dept=='Scan')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:scan_dashboard.php");

                    }
                    else if($doc_dept=='Laboratory')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:Lab_dashboard.php");

                    }
                    else if($doc_dept=='Doctor')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:doc/doctor_dashboard.php");

                    }
                    else if($doc_dept=='Vice Chancellor')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:vc_dashboard.php");

                    }
                    else if($doc_dept=='Radiology')
                    {
                        log_action($doc_id,"LOGIN");
                        header("location:radiology_dashboard.php");

                    }
            }

        else
            {
            #echo "<script>alert('Access Denied Please Check Your Credentials');</script>";
                $err = "Access Denied Please Check Your Credentials";
            }


       
    }


function pharmacyopeningstock($date,$mysqli){
    $campus_id = isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : null;
    $hasCampusCol = 0;
    if ($campus_id) {
        $resCol = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_stock' AND COLUMN_NAME='campus_id'");
        if ($resCol) {
            $rowCol = $resCol->fetch_assoc();
            $hasCampusCol = isset($rowCol['cnt']) ? (int)$rowCol['cnt'] : 0;
        }
    }

    if ($hasCampusCol && $campus_id) {
        $sql="SELECT * FROM pharmacy_stock where date='$date' AND campus_id=".(int)$campus_id; 
    } else {
        $sql="SELECT * FROM pharmacy_stock where date='$date'"; 
    }

   $result = mysqli_query($mysqli,$sql);
    $num=mysqli_num_rows($result);
        if($num>0){


        }
        else{
            $sqll="SELECT * FROM pharmacy order by id ASC"; 
            $results = mysqli_query($mysqli,$sqll);
            while($reply = mysqli_fetch_array($results)){
                $name=$reply['name'];
                $qnt=$reply['quantity'];
                if ($hasCampusCol && $campus_id) {
                    $quey="insert into pharmacy_stock(name,opening,addstock,closing,date,campus_id) values('$name','$qnt','0','$qnt','$date','".(int)$campus_id."')";
                } else {
                    $quey="insert into pharmacy_stock(name,opening,addstock,closing,date) values('$name','$qnt','0','$qnt','$date')";
                }
                $st2 = mysqli_query($mysqli,$quey);
                           
                        }
            }

    }

function storeopeningstock($date,$mysqli){
    $sql="SELECT * FROM store_stock where date='$date'"; 
   $result = mysqli_query($mysqli,$sql);
    $num=mysqli_num_rows($result);
    if($num>0){


    }
    else{
        $sqll="SELECT * FROM drug order by id ASC"; 
        $results = mysqli_query($mysqli,$sqll);
        while($reply = mysqli_fetch_array($results)){
                $name=$reply['name'];
                $qnt=$reply['quantity'];
                $quey="insert into store_stock(name,opening,addstock,closing,date) values('$name','$qnt','0','$qnt','$date')";
                $st2 = mysqli_query($mysqli,$quey);
                           
                }
            }

    
}

function getcampusid($campusname,$mysqli){
    // If the posted value is numeric, assume it's already the campus id.
    if (is_numeric($campusname)) {
        return (int) $campusname;
    }

    // Otherwise look up campus id by name using a prepared statement.
    $stmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $campusname);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return (int) $row['id'];
    }
    return null;
}
?>
<!--End Login-->
<!DOCTYPE html>
<html lang="en">
    
<head>
        <meta charset="utf-8" />
        <title>OOU Hospital Management System | Login Portal</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="" name="description" />
        <meta content="" name="MartDevelopers" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/oou.png">

        <!-- App css -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
        <!--Load Sweet Alert Javascript-->
        
        <script src="assets/js/swal.js"></script>
        <!--Inject SWAL-->
        <?php if(isset($success)) {?>
        <!--This code for injecting an alert-->
                <script>
                            setTimeout(function () 
                            { 
                                swal("Success","<?php echo $success;?>","success");
                            },
                                100);
                </script>

        <?php } ?>

        <?php if(isset($err)) {?>
        <!--This code for injecting an alert-->
                <script>
                            setTimeout(function () 
                            { 
                                swal("Failed","<?php echo $err;?>","Failed");
                            },
                                100);
                </script>

        <?php } ?>



    </head>

    <body class="authentication-bg authentication-bg-pattern" >

        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card bg-pattern">

                            <div class="card-body p-4" >
                                
                                <div class="text-center w-75 m-auto">
                                    <a href="index.php">
                                        <span><img src="assets/images/OOU.png" alt="" height="46"></span>
                                        <span><img src="assets/images/logo-dark.png" alt="" height="22"></span>
                                    </a>
                                    <p class="text-muted mb-4 mt-3">Enter your username and password to access your portal.</p>
                                </div>

                                <form method='post' >

                                    <div class="form-group mb-3">
                                        <label for="emailaddress">Staff Number</label>
                                        <input class="form-control" name="ad_id" type="text" id="emailaddress" required="" placeholder="Enter your number">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="password">Password</label>
                                        <input class="form-control" name="ad_pwd" type="password" required="" id="password" placeholder="Enter your password">
                                    </div>
                                     <div class="form-group mb-3">
                                                <!-- Pharmacy location selector inline with prescription fields -->
                                                    
                                                                <label for="pharmacy_location_select"> Select Campus Location</label>
                                                                <?php if (!empty($pharmacy_location_name)): ?>
                                                                    <small class="form-text text-muted">Current: <?php echo htmlspecialchars($pharmacy_location_name); ?></small>
                                                                <?php endif; ?>
                                                                <select id="pharmacy_location_select" name="location" class="form-control" required>
                                                                    <option value="">-- Select --</option>
                                                                    <?php
                                                                    $pl_res = $mysqli->query("SELECT id, name FROM campus_locations ORDER BY name ASC");
                                                                    if ($pl_res) {
                                                                        while ($pl = $pl_res->fetch_assoc()) {
                                                                            $selected = (!empty($pharmacy_location_id) && (int)$pharmacy_location_id === (int)$pl['id']) ? 'selected' : '';
                                                                            echo "<option value='".intval($pl['id'])."' " . $selected . ">".htmlspecialchars($pl['name'])."</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                              

                                    </div>

                                                                            <hr>
                                    <div class="form-group mb-0 text-center">
                                        <button name="admin_login" type="submit" class="ladda-button btn btn-primary"  data-style="expand-right"> Clinic Login Only </button>
                                    </div>

                                </form>

                                <!--
                                For Now Lets Disable This 
                                This feature will be implemented on later versions
                                <div class="text-center">
                                    <h5 class="mt-3 text-muted">Sign in with</h5>
                                    <ul class="social-list list-inline mt-3 mb-0">
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-primary text-primary"><i class="mdi mdi-facebook"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-danger text-danger"><i class="mdi mdi-google"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-info text-info"><i class="mdi mdi-twitter"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-secondary text-secondary"><i class="mdi mdi-github-circle"></i></a>
                                        </li>
                                    </ul>
                                </div> 
                                -->

                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->

                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <p> <a href="his_admin_pwd_reset.php" class="text-white-50 ml-1">Forgot your password?</a></p>
                               <!-- <p class="text-white-50">Don't have an account? <a href="his_admin_register.php" class="text-white ml-1"><b>Sign Up</b></a></p>-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->


        <?php include ("assets/inc/footer1.php");?>

        <!-- Vendor js -->
        <script src="assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="assets/js/app.min.js"></script>
        
    </body>

</html>