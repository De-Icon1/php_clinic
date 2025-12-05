<?php
	session_start();
	include('assets/inc/config.php');
    include('assets/inc/checklogins.php');
  check_login();
  authorize();
  $aid=$_SESSION['doc_id'];
   $doc_number = $_SESSION['doc_number'];

    if(isset($_GET['del'])){
     //sql to insert captured values
    $id=$_GET['del'];
            $query="delete from pcart where id=?";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('s',$id);
            $stmt->execute();
            if($stmt)
            {
                $success = "Item Deleted Successfully";

            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}
$gamnt=0;
$relt="select * from pharmacy_order where trackid=''";
if(isset($_GET['rel'])){
     //sql to insert captured values
    $tid=$_GET['rel'];
    $date=$_GET['date'];
    $relt="select * from pharmacy_order where trackid='$tid' and date='$date'";
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
                $success = "Payment Deleted Successfully";

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

         $teller="PHV".rand(0,7829); 


         function getcarttot($mysqli){
            $date=date('Y-m-d');
            $bal=0;
            $result=mysqli_query($mysqli,"select * from pcart where date='$date'");
            while($reply = mysqli_fetch_array($result)){
                $amnt=$reply['amount'];
                $bal+=$amnt;
            }
            return $bal;
        }

function getphartot($mysqli,$date,$tid){
            $bal=0;
            $result=mysqli_query($mysqli,"select * from pharmacy_order where trackid='$tid' and date='$date'");
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

if(isset($_POST['pcart'])){

        $date=date('Y-m-d');
        $qnt=$_POST['qnt'];
        $drug=$_POST['drug'];

           $query=mysqli_query($mysqli,"SELECT * FROM drug where name='$drug'");
            $row=mysqli_fetch_array($query);
            $nm=mysqli_num_rows($query);
            if($nm > 0){
               $amnt=$row['amount'];
               $sql="insert into pcart values(0,'$date','$drug','$qnt','$amnt')";
               $sq=mysqli_query($mysqli,$sql); 
            }

}
if(isset($_POST['porder'])){

        $date=date('Y-m-d');
       $inv=$_POST['teller'];
       $cname=$_POST['name'];

           $query=mysqli_query($mysqli,"SELECT * FROM pcart where date='$date'");
            $row=mysqli_fetch_array($query);
            $nm=mysqli_num_rows($query);
            if($nm > 0){
            /*while($reply = mysqli_fetch_array($query)){
                $drug=$reply['drug'];
                $qnt=$reply['qnt'];
            $amnt=$reply['amount'];

               $sql="insert into pharmacy_order values(0,'$inv','$cname','$drug','$qnt','','$amnt','Not Paid','$date')";
               $sq=mysqli_query($mysqli,$sql); */


$ret="SELECT * FROM pcart where date='$date'";
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                                    $drug=$row->drug;
                                                    $qnt=$row->qnt;
                                                   $amnt =$row->amount;

                                                   $sql="insert into pharmacy_order values(0,'$inv','$cname','$drug','$qnt','','$amnt','Not Paid','$date')";
                                                   $sq=mysqli_query($mysqli,$sql); 
                                                 }   
                                            

                                                
           
        }

          $sql="delete from pcart";
               $sq=mysqli_query($mysqli,$sql); 

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
                <?php include('assets/inc/sidebar_ph.php');?>
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
                                                    PURCHASE ORDER
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#settings" data-toggle="tab" aria-expanded="false" class="nav-link">
                                                   DISPENSE ORDER 
                                                </a>
                                            </li>
                                            
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane show active" id="aboutme">

                                            <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                                    <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle mr-1"></i>PATIENT ORDER</h5>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="product_key">Product Qty</label>
                                                                <input type="text" name="qnt"  class="form-control" id="firstname" placeholder="Enter product qty">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="constitution">Constitution</label>
                                                                   <select id="inputState" required="required" name="const" onChange="getDrug(this.value);" class="form-control">
                                                            <option>Choose</option>
                                                             <option value="Tab">Tab</option>
                                                            <option value="Syrup">Syrup</option>
                                                            <option value="Injection">Injection</option>
                                                            <option value="Cream">Cream</option>
                                                     
                                                    </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="drugs">Drugs</label>
                                                               <select id="name" required="required" name="drug"  class="form-control">
                                                        <option>Choose</option>
                                                         
                                                        
                                                    </select>
                                                            </div>
                                                        </div> <!-- end col -->


                                                    </div> <!-- end row -->
                                                        <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="text-right">
                                                            <button type="submit" name="pcart"   class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Pharmacy Order</button>
                                                            </div>
                                                        </div>
                                                        
                                                       

                                                        
                                                    </div> <!-- end row -->
                                                    
                                                    <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                            <thead>
                                            <tr>
                                              
                                                <th data-hide="phone" style="color:white;">Date</th>
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
                                                $ret="SELECT * FROM pcart ORDER BY id ASC "; 
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
                                                    <td><?php echo $row->drug;?></td>
                                                    <td><?php echo $row->qnt;?></td>
                                                    <td><?php echo number_format($row->amount,2);?></td>
                                                    
                                                    <td><a href="pharmacy_order.php?del=<?php echo $row->id;?>"><img src="assets/img/remove.png" height="20" width="20"></a></td>
                                                </tr>
                                                </tbody>
                                            <?php $cnt = $cnt +1 ; }?>
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
                                                    <div class="col-md-3">
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
                                                    <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="lastname">Customer Name</label>
                                                                <input type="text" class="form-control" name="name" id="lastname" placeholder="Customer Name">
                                                            </div>

                                                    </div> 
                                                        </div>

                                                        <div class="row">
                                               
                                                        

                                                            <div class="text-right">
                                                            <button type="submit" name="porder"   class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Make Order Now</button>
                                                            </div>
                                                    </div> <!-- end row -->

                                                    
                                                    <!-- end row -->

                                                    <!--
                                                    <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-office-building mr-1"></i> Company Info</h5>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="companyname">Company Name</label>
                                                                <input type="text" class="form-control" id="companyname" placeholder="Enter company name">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="cwebsite">Website</label>
                                                                <input type="text" class="form-control" id="cwebsite" placeholder="Enter website url">
                                                            </div>
                                                        </div> 
                                                    </div> 

                                                    <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-earth mr-1"></i> Social</h5>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="social-fb">Facebook</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fab fa-facebook-square"></i></span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="social-fb" placeholder="Url">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="social-tw">Twitter</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="social-tw" placeholder="Username">
                                                                </div>
                                                            </div>
                                                        </div> 
                                                    </div> 

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="social-insta">Instagram</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="social-insta" placeholder="Url">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="social-lin">Linkedin</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="social-lin" placeholder="Url">
                                                                </div>
                                                            </div>
                                                        </div> 
                                                    </div> 

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="social-sky">Skype</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fab fa-skype"></i></span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="social-sky" placeholder="@username">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="social-gh">Github</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fab fa-github"></i></span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="social-gh" placeholder="Username">
                                                                </div>
                                                            </div>
                                                        </div> 
                                                    </div>  -->
                                                    
                                                    
                                                </form>


                                            </div> <!-- end tab-pane -->
                                            <!-- end about me section content -->

                                           

                                            <div class="tab-pane" id="settings">
                                                <form method="post">
                                                    <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle mr-1"></i> DISPENSE ORDER</h5>
                                                    
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
                                                    <td><a href="pharmacy_order.php?rel=<?php echo $row->trackid;?>&date=<?php echo $row->date;?>"><img src="assets/img/good.png" height="20" width="20"></a></td>
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
                                                    
                                                    <td><a href="pharmacy_order.php?del=<?php echo $row->id;?>"><img src="assets/img/remove.png" height="20" width="20"></a></td>
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
                                                   
                                                    
                                                
                                                    </div> <!-- end row -->

                                                    <div class="text-right">
                                                        <button type="submit" name="pharmacy" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i>Dispence Order</button>
                                                    </div>
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

        function getDrug(val) {
    $.ajax({
    type: "POST",
    url: "get_drug.php",
    data:'name='+val,
    success: function(data){
        $("#name").html(data);
    }
    });
}

    </script>
    <script type="text/javascript">

        function getCarts(val) {
    $.ajax({
    type: "POST",
    url: "get_carts.php",
    data:'cart='+val,
    success: function(data){
        $("#cart").html(data);
    }
    });
     location.reload()
}

    </script>
</html>