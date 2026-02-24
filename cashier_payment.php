<?php
	session_start();
	include('assets/inc/config.php');
    include('assets/inc/checklogins.php');
  check_login();
  authorize();
  $aid=$_SESSION['doc_id'];
   $doc_number = $_SESSION['doc_number'];
   $campusid=$_SESSION['campus_id'];

   function getcampus($campusid,$mysqli){
        $sql="SELECT * FROM campus_locations where id=$campusid"; 
       $result = mysqli_query($mysqli,$sql);
        $num=mysqli_num_rows($result);
        $reply = mysqli_fetch_array($result);
        $name=$reply['name'];
        return $name;
    }   

    // Campus-aware pharmacy_order handling
    $campus_id = isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : null;
    $order_has_campus = 0;
    if ($campus_id) {
            $resCol = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_order' AND COLUMN_NAME='campus_id'");
            if ($resCol) {
                    $rowCol = $resCol->fetch_assoc();
                    $order_has_campus = isset($rowCol['cnt']) ? (int) $rowCol['cnt'] : 0;
            }
    }

    if(isset($_GET['del'])){
     //sql to insert captured values
    $id=$_GET['del'];
            $query="delete from cart where id=?";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('s',$id);
            $stmt->execute();
            if($stmt)
            {
                $success = "Item Deleted Sussefully";

            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}
$gamnt=0;
// Default listing of pharmacy_order, optionally scoped by campus
if ($order_has_campus && $campus_id) {
    $relt = "select * from pharmacy_order where trackid='' AND campus_id=" . (int)$campus_id;
} else {
    $relt="select * from pharmacy_order where trackid=''";
}
if(isset($_GET['rel'])){
     //sql to insert captured values
    $tid=$_GET['rel'];
    $date=$_GET['date'];
    if ($order_has_campus && $campus_id) {
        $relt="select * from pharmacy_order where trackid='$tid' and date='$date' AND campus_id=" . (int)$campus_id;
    } else {
        $relt="select * from pharmacy_order where trackid='$tid' and date='$date'";
    }
    $gamnt=getphartot($mysqli,$date,$tid);
}



if(isset($_GET['dels'])){

     //sql to insert captured values
    $id=$_GET['dels'];
            $query="delete from cart_pay where id=?";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('s',$id);
            $stmt->execute();
            if($stmt)
            {
                $success = "Payment Deleted Sussefully";

            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}


        if(isset($_POST['cpay'])){
            $date=date('Y-m-d');
            $mode=$_POST['mode'];
            $bank=$_POST['bank'];
            $code=getbankacc($mysqli,$bank);
            $amnt=$_POST['amnt'];
            $sql="insert into cart_pay values(0,'$date','$bank','$code','$amnt','$mode')";
            $sq=mysqli_query($mysqli,$sql); 

        }

         $teller="INV".rand(0,7829); 


         function getcarttot($mysqli){
            $date=date('Y-m-d');
            $bal=0;
            $result=mysqli_query($mysqli,"select * from cart where date='$date'");
            while($reply = mysqli_fetch_array($result)){
                $amnt=$reply['amount'];
                $bal+=$amnt;
            }
            return $bal;
        }

function getphartot($mysqli,$date,$tid){
            $bal=0;
            $campus_id = isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : null;
            $order_has_campus = 0;
            if ($campus_id) {
                $resCol = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_order' AND COLUMN_NAME='campus_id'");
                if ($resCol) {
                    $rowCol = $resCol->fetch_assoc();
                    $order_has_campus = isset($rowCol['cnt']) ? (int) $rowCol['cnt'] : 0;
                }
            }
            if ($order_has_campus && $campus_id) {
                $sql = "select * from pharmacy_order where trackid='$tid' and date='$date' and campus_id=" . (int)$campus_id;
            } else {
                $sql = "select * from pharmacy_order where trackid='$tid' and date='$date'";
            }
            $result=mysqli_query($mysqli,$sql);
            while($reply = mysqli_fetch_array($result)){
                $amnt=$reply['amount'];
                $bal+=$amnt;
            }
            return $bal;
        }

         function getcartpay($mysqli){
            $date=date('Y-m-d');
            $bal=0;
            $result=mysqli_query($mysqli,"select * from cart_pay where date='$date'");
            while($reply = mysqli_fetch_array($result)){
                $amnt=$reply['amount'];
                $bal+=$amnt;
            }
            return $bal;
        }
        function getbankacc($mysqli,$bank){
            $date=date('Y-m-d');
            $result=mysqli_query($mysqli,"select * from chart where name='$bank'");
            $reply = mysqli_fetch_array($result);
                $code=$reply['code'];
            
            return $code;
        }
 function getofficer($mysqli,$id){
            $result=mysqli_query($mysqli,"select * from his_docs where doc_number='$id'");
            $reply = mysqli_fetch_array($result);
                $fname=$reply['doc_fname'];
                    $lname=$reply['doc_lname'];
                    $name=$fname." ".$lname;
            
            return $name;
        }
if(isset($_POST['payment'])){
            $amnt=$_POST['totamnt'];
             $gamnt=$_POST['gamnt'];
            $customer=$_POST['name'];
            $date=date('Y-m-d');
            $mon=date('m');
            $yr=date('Y');
            $time=date("h:i a" );
            $teller=$_POST['teller'];
            $officer=getofficer($mysqli,$doc_number);
    if($gamnt > $amnt || $gamnt < $amnt){
        $err = "Payment Not Complete,Please Try Again";
    }
    else{
            $sql =$mysqli->query("select * from cart where date='$date' order by id ASC");
                while($reply = mysqli_fetch_array($sql)){
                        $details=$reply['details'];
                        $amount=$reply['amount'];
                         
                            $inv =$mysqli->query("insert into invoice values(0,'$date','$teller','$customer','$details','$time','$amount','$amount','$officer')");

                    }

            $sql2 =$mysqli->query("select * from cart_pay where date='$date' order by id ASC");
                while($replys = mysqli_fetch_array($sql2)){
                        $bank=$replys['bank'];
                        $account=$replys['account'];
                        $amount=$replys['amount'];
                        $mode=$replys['mode'];
                         
                              $sqs =$mysqli->query("insert into payment values(0,'$date','$teller','$customer','$bank','$mode','$amount','$officer')");
                               $csh =$mysqli->query("insert into cashbook values(0,'$date','$teller','$time','$customer','$amount','0','0','0','$officer')");
                                $jorn =$mysqli->query("insert into journal values(0, '$date','100','Cash Account','0','$amount','$officer')");
                                $jorn2 =$mysqli->query("insert into journal values(0, '$date','101','Sales Account','$amount','0','$officer')");
                    }
                    echo "<script>location='receipt.php?inv=$teller&date=$date'</script>";
                     $sqls =$mysqli->query("delete from cart");
                      $sls =$mysqli->query("delete from cart_pay");

        }
    }
                            

if(isset($_POST['pharmacy'])){
            $tid=$_GET['rel'];
            $date=$_GET['date'];
            $tamntp=$_POST['tamntp'];
             $amntp=$_POST['amntp'];
                                           
            $time=date("h:i a" );
            $officer=getofficer($mysqli,$doc_number);
    if($tamntp > $amntp || $tamntp < $amntp){
        $err = "Payment Not Complete,Please Try Again";
    }
    else{
            $sql =$mysqli->query("select * from pharmacy_order where trackid='$tid' and date='$date' order by id ASC");
                while($reply = mysqli_fetch_array($sql)){
                        $customer=$reply['customer'];
                        $amount=$reply['amount'];
                         $drug=$reply['drug'];
                          $qnt=$reply['Qnt'];
                           $const=$reply['const'];
                         
                            $inv =$mysqli->query("insert into pharmacy_invoice values(0,'$date','$time','$customer','$tid','$drug','$qnt','$amount','$const','$officer')");

                    }

            $sql2 =$mysqli->query("select * from pharmacy_order where date='$date' and trackid='$tid' order by id ASC");
                while($replys = mysqli_fetch_array($sql2)){
                         $customer=$replys['customer'];
                        $account=$replys['account'];
                       

                        $bankp=$_POST['bankp'];
                        $accountp=getbankacc($mysqli,$bankp);
                        $amntp=$_POST['amntp'];
                        $modep=$_POST['modep'];
                         
                              $sqs =$mysqli->query("insert into payment values(0,'$date','$tid','$customer','$bankp','$modep','$amntp','$officer')");
                               $csh =$mysqli->query("insert into cashbook values(0,'$date','$tid','$time','$customer','$amntp','0','0','0','$officer')");
                                $jorn =$mysqli->query("insert into journal values(0, '$date','100','Cash Account','0','$amntp','$officer')");
                                $jorn2 =$mysqli->query("insert into journal values(0, '$date','101','Sales Account','$amntp','0','$officer')");
                    }
                    echo "<script>location='phar_receipt.php?inv=$tid&date=$date'</script>";
                     $sqls =$mysqli->query("update pharmacy_order set status='Paid' where trackid='$tid'");

        }
    }
                        
?>
<!DOCTYPE html>
    <html lang="en">
        <?php include('assets/inc/head.php');?>
    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
            <?php include('assets/inc/nav_r.php');?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
                <?php include('assets/inc/sidebar_cash.php');?>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            <?php
                $doc_id=$_SESSION['doc_id'];
                $ret="SELECT * FROM  his_docs where doc_id=?";
                $stmt= $mysqli->prepare($ret) ;
                $stmt->bind_param('i',$doc_id);
                $stmt->execute() ;//ok
                $res=$stmt->get_result();
                //$cnt=1;
                while($row=$res->fetch_object())
                {
            ?>
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
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                                <li class="breadcrumb-item active">Profile</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title"><?php echo $row->doc_fname;?> <?php echo $row->doc_lname;?>'s Profile</h4>
                                    </div>
                                </div>
                            </div>
                            <!-- end page title -->

                            <div class="row">
                            <div class="col-lg-4 col-xl-4">
                                <div class="card-box text-center">
                                    <img src="assets/images/users/<?php echo $row->doc_dpic;?>" class="rounded-circle avatar-lg img-thumbnail"
                                        alt="profile-image">

                                    
                                    <div class="text-centre mt-3">
                                        
                                        <p class="text-muted mb-2 font-13"><strong>Employee Full Name :</strong> <span class="ml-2"><?php echo $row->doc_fname;?> <?php echo $row->doc_lname;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Employee Department :</strong> <span class="ml-2"><?php echo $row->doc_dept;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Employee Number :</strong> <span class="ml-2"><?php echo $row->doc_number;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Employee Email :</strong> <span class="ml-2"><?php echo $row->doc_email;?></span></p>


                                    </div>

                                </div> <!-- end card-box -->

                            </div> <!-- end col-->

                                <div class="col-lg-8 col-xl-8">
                                    <div class="card-box">
                                        <ul class="nav nav-pills navtab-bg nav-justified">
                                            <li class="nav-item">
                                                <a href="#aboutme" data-toggle="tab" aria-expanded="false" class="nav-link active">
                                                    PATIENT PAYMENT PORTAL
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#settings" data-toggle="tab" aria-expanded="false" class="nav-link">
                                                   PAYMENT FOR PHARMACY ORDER 
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#sets" data-toggle="tab" aria-expanded="false" class="nav-link">
                                                   DOCTOR'S PATIENT REFERRAL 
                                                </a>
                                            </li>
                                            
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane show active" id="aboutme">

                                            <form method="post" enctype="multipart/form-data">
                                                    <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle mr-1"></i>PATIENT PAY NOW</h5>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Patient ID</label>
                                                                <input type="text" name="code"  class="form-control" id="firstname" placeholder="Enter Patient ID">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="firstname">Payment Category</label>
                                                                   <select id="inputState" required="required" name="cate" onChange="getPrice(this.value);" class="form-control">
                                                            <option>Choose</option>
                                                             <option value="Card">Card</option>
                                                            <option value="Laboratory">Laboratory</option>
                                                            <option value="Scan">Scan</option>
                                                            <option value="Nursing">Patient Bill</option>
                                                     
                                                    </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="lastname">Sub Categories</label>
                                                               <select id="name" required="required" name="scate" onChange="getCart(this.value);" class="form-control">
                                                        <option>Choose</option>
                                                         
                                                        
                                                    </select>
                                                            </div>
                                                        </div> <!-- end col -->


                                                    </div> <!-- end row -->
                                                        <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="firstname">Customer's Fullname</label>
                                                                <input type="text" name="name" class="form-control" id="firstname" placeholder="Enter Customer's Name">
                                                            </div>
                                                        </div>
                                                        
                                                       

                                                        
                                                    </div> <!-- end row -->
                                                    
                                                    <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                            <thead>
                                            <tr>
                                              
                                                <th data-hide="phone" style="color:white;">Date</th>
                                                <th data-hide="phone" style="color:white;">Details</th>
                                                <th data-hide="phone" style="color:white;">Amount</th>
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM cart ORDER BY id ASC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->details;?></td>
                                                    <td><?php echo number_format($row->amount,2);?></td>
                                                    
                                                    <td><a href="cashier_payment.php?del=<?php echo $row->id;?>"><img src="assets/img/remove.png" height="20" width="20"></a></td>
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
                                    </div>
                                                <div class="row">
                                                    <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="lastname">Invoice No</label>
                                                                <input type="text" class="form-control" name="teller" id="lastname" style="color:green;" value="<?php echo $teller ?>" placeholder="Total Amount">
                                                            </div>
                                                    </div> 
                                                    <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="lastname">Grand Total</label>
                                                                <input type="text" class="form-control" style="color:BLUE; font-size:larger; " disabled name="gamnt" value="<?php echo number_format(getcarttot($mysqli),2); ?>" id="lastname" placeholder="Total Amount">
                                                            </div>
                                                    </div> 
                                                        
                                                <div class="col-md-2">
                                                    <label for="inputState">Mode</label>
                                                    <select id="inputState" required="required" name="mode" class="form-control">
                                                        <option>Choose</option>
                                                         <option value="Cash">Cash</option>
                                                        <option value="Transfer">Transfer</option>
                                                        <option value="POS">POS</option>
                                                        
                                                    </select>
                                                </div>

                                                <div class="col-md-2">
                                                    <label for="inputState">Banks</label>
                                                    <select id="inputState" required="required" name="bank" class="form-control">
                                                        <option>Choose</option>
                                                      <?php
                                                            $sql = "SELECT * FROM chart order by id ASC";
                                                            $result = mysqli_query($mysqli,$sql);
                                                            while($reply = mysqli_fetch_array($result)){
                                                                echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";
                                                            }
                                                        ?>
                                                        
                                                    </select>
                                                </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="lastname">Paid Amount</label>
                                                                <input type="text" class="form-control" style="color:RED; font-size:larger; " name="amnt" id="lastname" placeholder="Enter Payment">
                                                            </div>
                                                        </div> 

                                                            <div class="text-right">
                                                            <button type="submit" name="cpay"   class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Add To Cart</button>
                                                            </div>
                                                    </div> <!-- end row -->

                                                     <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle mr-1"></i>PAYMENT DETAILS</h5>
                                                     <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                            <thead>
                                            <tr>
                                              
                                                <th data-hide="phone" style="color:white;">Date</th>
                                                <th data-hide="phone" style="color:white;">Bank</th>
                                                <th data-hide="phone" style="color:white;">Account</th>
                                                <th data-hide="phone" style="color:white;">Amount</th>
                                                <th data-hide="phone" style="color:white;">Mode</th>
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM cart_pay ORDER BY id ASC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->bank;?></td>
                                                    <td><?php echo $row->account;?></td>
                                                    <td><?php echo number_format($row->amount,2);?></td>
                                                    <td><?php echo $row->mode;?></td>
                                                    <td><a href="cashier_payment.php?dels=<?php echo $row->id;?>"><img src="assets/img/remove.png" height="20" width="20"></a></td>
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
                                    </div>

                                    <div class="row">
                                                    <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="lastname">Total Payment</label>
                                                                <input type="text" class="form-control" name="totamnt" id="lastname" style="color:BLUE; font-size:larger; " disabled value="<?php echo number_format(getcartpay($mysqli),2); ?>"placeholder="Total Amount">
                                                            </div>
                                                    </div> 
                                                </div>
                                                    <!-- end row -->

                                                 
                                                    
                                                    <div class="text-right">
                                                        <button type="submit" name="payment" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Pay Now</button>
                                                    </div>
                                                </form>


                                            </div> <!-- end tab-pane -->
                                            <!-- end about me section content -->

                                           

                                            <div class="tab-pane" id="settings">
                                                <form method="post">
                                                    <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle mr-1"></i> PHARMACY ORDER</h5>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="firstname">Customer's Fullname</label>
                                                                <input type="text" name="name"  class="form-control" id="firstname" placeholder="Enter Costomer's Name">
                                                            </div>
                                                        </div>
                                                        
                                                       

                                                        
                                                    </div>
                                                        <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                            <thead>
                                            <tr>
                                              
                                                <th data-hide="phone" style="color:white;">Date</th>
                                                <th data-hide="phone" style="color:white;">Trackid</th>
                                                <th data-hide="phone" style="color:white;">Name</th>
                                                <th data-hide="phone" style="color:white;">Drug</th>
                                                <th data-hide="phone" style="color:white;">Status</th>
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM pharmacy_order ORDER BY id ASC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->trackid;?></td>
                                                    <td><?php echo $row->customer;?></td>
                                                    <td><?php echo $row->drug;?></td>
                                                    <td><?php echo $row->status;?></td>
                                                    <td><a href="cashier_payment.php?rel=<?php echo $row->trackid;?>&date=<?php echo $row->date;?>"><img src="assets/img/good.png" height="20" width="20"></a></td>
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
                                    </div>


                                    <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                            <thead>
                                            <tr>
                                              
                                                <th data-hide="phone" style="color:white;">Date</th>
                                                <th data-hide="phone" style="color:white;">Trackid</th>
                                                <th data-hide="phone" style="color:white;">Drug</th>
                                                <th data-hide="phone" style="color:white;">Qty</th>
                                                <th data-hide="phone" style="color:white;">Amount</th>
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                //$ret="SELECT * FROM cart ORDER BY id ASC "; 

                                                $stmt= $mysqli->prepare($relt) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->trackid;?></td>
                                                    <td><?php echo $row->drug;?></td>
                                                    <td><?php echo $row->Qnt;?></td>
                                                    <td><?php echo number_format($row->amount,2);?></td>
                                                    
                                                    <td><a href="cashier_payment.php?del=<?php echo $row->id;?>"><img src="assets/img/remove.png" height="20" width="20"></a></td>
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
                                    </div>
                                                     <!-- end row -->

                                                    <div class="row">
                                                   
                                                    <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="lastname">Grand Total</label>
                                                                <input type="text" class="form-control" style="color:BLUE; font-size:larger; " name="tamntp" value="<?php echo number_format($gamnt,2); ?>" id="lastname" placeholder="Total Amount">
                                                            </div>
                                                    </div> 
                                                        
                                                <div class="col-md-3">
                                                    <label for="inputState">Mode</label>
                                                    <select id="inputState" required="required" name="modep" class="form-control">
                                                        <option>Choose</option>
                                                         <option value="Cash">Cash</option>
                                                        <option value="Transfer">Transfer</option>
                                                        <option value="POS">POS</option>
                                                        
                                                    </select>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="inputState">Banks</label>
                                                    <select id="inputState" required="required" name="bankp" class="form-control">
                                                        <option>Choose</option>
                                                      <?php
                                                            $sql = "SELECT * FROM chart order by id ASC";
                                                            $result = mysqli_query($mysqli,$sql);
                                                            while($reply = mysqli_fetch_array($result)){
                                                                echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";
                                                            }
                                                        ?>
                                                        
                                                    </select>
                                                </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="lastname">Paid Amount</label>
                                                                <input type="text" class="form-control" style="color:RED; font-size:larger; " name="amntp" id="lastname" placeholder="Enter Payment">
                                                            </div>
                                                        </div> 
                                                        
                                                    </div> <!-- end row -->

                                                    <div class="text-right">
                                                        <button type="submit" name="pharmacy" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i>Pay Pharmacy Order</button>
                                                    </div>
                                                </form>
                                            </div>
                                            <!-- end settings content-->


                                            <!-- end tab-pane -->
                                            <!-- end about me section content -->

                                           

                                            <div class="tab-pane" id="sets">
                                                <form method="post">
                                                    <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle mr-1"></i> DOCTORS REFERRAL</h5>
                                                   
                                                        <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                            <thead>
                                            <tr>
                                              
                                               <th data-hide="phone" style="color:white;">S/N</th>
                                                <th data-hide="phone" style="color:white;">Date</th>
                                                <th data-hide="phone" style="color:white;">Name</th>
                                                <th data-hide="phone" style="color:white;">Test</th>

                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM refer ORDER BY id ASC "; 
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                     <td><?php echo $cnt;?></td>
                                                    <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->name;?></td>
                                                    <td><?php echo $row->test;?></td>
                                                    
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
                                    </div>


                                                                                         <!-- end row -->

                                                   
                                                   
                                                </form>
                                            </div>
                                            <!-- end settings content-->



                                        </div> <!-- end tab-content -->
                                    </div> <!-- end card-box-->

                                </div> <!-- end col -->
                            </div>
                            <!-- end row-->

                        </div> <!-- container -->

                    </div> <!-- content -->

                    <!-- Footer Start -->
                    <?php include("assets/inc/footer.php");?>
                    <!-- end Footer -->

                </div>
            <?php }?>
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

        
        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- Vendor js -->
        <script src="assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="assets/js/app.min.js"></script>

    </body>
<script type="text/javascript">

        function getPrice(val) {
    $.ajax({
    type: "POST",
    url: "get_price.php",
    data:'name='+val,
    success: function(data){
        $("#name").html(data);
    }
    });
}

    </script>
    <script type="text/javascript">

        function getCart(val) {
    $.ajax({
    type: "POST",
    url: "get_cart.php",
    data:'cart='+val,
    success: function(data){
        $("#cart").html(data);
    }
    });
     location.reload()
}

    </script>
</html>