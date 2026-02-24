<?php
    session_start();
    include('assets/inc/config.php');//get configuration file

if(isset($_POST['nursing_login']))
    {

        $doc_number = $_POST['ad_id'];
        //$doc_email = $_POST['doc_ea']
        $doc_dept='Nursing';
        $doc_pwd = sha1(md5($_POST['ad_pwd']));//double encrypt to increase security
        $stmt=$mysqli->prepare("SELECT doc_number, doc_pwd, doc_id, doc_dept FROM his_docs WHERE  doc_number=? AND doc_pwd=? AND doc_dept=?");//sql to log in user
        $stmt->bind_param('sss', $doc_number, $doc_pwd, $doc_dept); //bind fetched parameters
        $stmt->execute();//execute bind
        $stmt -> bind_result($doc_number, $doc_pwd, $doc_id, $doc_dept); //bind result
        $rs=$stmt->fetch();
        $_SESSION['doc_id'] = $doc_id;
        $_SESSION['doc_number'] = $doc_number; //Assign session to doc_number id
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
                        $_SESSION['campus_id'] = $campus_id;
                        $_SESSION['working_location_id'] = $campus_id;
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
        if($rs)
            {//if its sucessfull
                header("location:nursing_dashboard.php");        
                  }

        else
            {
            #echo "<script>alert('Access Denied Please Check Your Credentials');</script>";
                $err = "Access Denied Please Check Your Credentials";
            }

       
    }

  
?>
<!--End Login-->
<!DOCTYPE html>
<html lang="en">
    
<head>
        <meta charset="utf-8" />
        <title>Hospital Management System -Nursing Login Portal</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="" name="description" />
        <meta content="" name="MartDevelopers" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

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

    <body class="authentication-bg authentication-bg-pattern">

        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card bg-pattern">

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">
                                    <a href="index.php">
                                        <span><img src="assets/images/logo-dark.png" alt="" height="22"></span>
                                    </a>
                                    <p class="text-muted mb-4 mt-3">Enter your Staffid and password to access nursing panel.</p>
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

                                    <div class="form-group mb-0 text-center">
                                        <button class="btn btn-primary btn-block" name="nursing_login" type="submit"> Nursing Log In </button>
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