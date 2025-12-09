<?php
  session_start();
  include('assets/inc/config.php');
  include('assets/inc/checklogins.php');
  check_login();
  authorize();
  $aid=$_SESSION['doc_id'];
   $doc_number = $_SESSION['doc_number'];
  // Determine nurse's assigned campus/location (if available).
  // Older schema may not have `campus_id` on `his_docs`. Prefer session-based `working_location`.
  $campus_id = null;
  if (isset($_SESSION['working_location']) && !empty($_SESSION['working_location'])) {
      $working_location = $_SESSION['working_location'];
      $campusStmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
      if ($campusStmt) {
          $campusStmt->bind_param('s', $working_location);
          $campusStmt->execute();
          $cres = $campusStmt->get_result();
          if ($crow = $cres->fetch_assoc()) {
              $campus_id = $crow['id'];
          }
      }
  }

  // If the DB schema has `his_docs.campus_id`, prefer that for the logged-in doctor/nurse
  $col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='his_docs' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
  if ($col_exists) {
      $docCampusStmt = $mysqli->prepare("SELECT campus_id FROM his_docs WHERE doc_id = ? AND doc_number = ? LIMIT 1");
      if ($docCampusStmt) {
          $docCampusStmt->bind_param('is', $aid, $doc_number);
          $docCampusStmt->execute();
          $dres = $docCampusStmt->get_result();
          if ($drow = $dres->fetch_assoc()) {
              if (!empty($drow['campus_id'])) $campus_id = $drow['campus_id'];
          }
      }
  }

  // Handle picking a consumable by stock id (location-specific)
  $picked_item = null;
  if (isset($_POST['pick_consumable'])) {
      $stock_id = (int) ($_POST['stock_id'] ?? 0);
      $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? LIMIT 1");
      if ($pst) {
          $pst->bind_param('i', $stock_id);
          $pst->execute();
          $pres = $pst->get_result();
          if ($row = $pres->fetch_assoc()) {
              if ($campus_id && $row['campus_id'] != $campus_id) {
                  $err = "Selected stock item does not belong to your location.";
              } else {
                  $picked_item = $row;
              }
          } else {
              $err = "Consumable stock ID not found.";
          }
      }
  }

  // AJAX: pick consumable (returns JSON)
  if (isset($_POST['ajax']) && $_POST['ajax'] === 'pick') {
      header('Content-Type: application/json');
      $stock_id = (int) ($_POST['stock_id'] ?? 0);
      $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? LIMIT 1");
      if ($pst) {
          $pst->bind_param('i', $stock_id);
          $pst->execute();
          $pres = $pst->get_result();
          if ($row = $pres->fetch_assoc()) {
              if ($campus_id && $row['campus_id'] != $campus_id) {
                  echo json_encode(['success' => false, 'error' => 'Selected stock item does not belong to your location.']);
              } else {
                  echo json_encode(['success' => true, 'item' => $row]);
              }
          } else {
              echo json_encode(['success' => false, 'error' => 'Consumable stock ID not found.']);
          }
      } else {
          echo json_encode(['success' => false, 'error' => 'Server error preparing statement.']);
      }
      exit();
  }

  // Handle issuing picked stock (reduce quantity)
  if (isset($_POST['issue_stock'])) {
      $stock_id = (int) ($_POST['stock_id'] ?? 0);
      $issue_qty = (int) ($_POST['issue_qty'] ?? 0);
      if ($issue_qty <= 0) {
          $err = "Enter a valid quantity to issue.";
      } else {
          $gst = $mysqli->prepare("SELECT quantity, campus_id FROM nurse_consumable_stock WHERE id = ? LIMIT 1");
          $gst->bind_param('i', $stock_id);
          $gst->execute();
          $gres = $gst->get_result();
          if ($g = $gres->fetch_assoc()) {
              if ($campus_id && $g['campus_id'] != $campus_id) {
                  $err = "You cannot issue stock from another location.";
              } elseif ($g['quantity'] < $issue_qty) {
                  $err = "Not enough stock to issue.";
              } else {
                  $upd = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity = quantity - ? WHERE id = ?");
                  $upd->bind_param('ii', $issue_qty, $stock_id);
                  $upd->execute();
                  if ($upd) $success = "Issued $issue_qty item(s) successfully.";
                  // Refresh picked item for display
                  $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? LIMIT 1");
                  $pst->bind_param('i', $stock_id);
                  $pst->execute();
                  $picked_item = $pst->get_result()->fetch_assoc();
              }
          } else {
              $err = "Stock record not found.";
          }
      }
  }

  // AJAX: issue stock (returns JSON)
  if (isset($_POST['ajax']) && $_POST['ajax'] === 'issue') {
      header('Content-Type: application/json');
      $stock_id = (int) ($_POST['stock_id'] ?? 0);
      $issue_qty = (int) ($_POST['issue_qty'] ?? 0);
      if ($issue_qty <= 0) {
          echo json_encode(['success' => false, 'error' => 'Enter a valid quantity to issue.']); exit();
      }
      $gst = $mysqli->prepare("SELECT quantity, campus_id FROM nurse_consumable_stock WHERE id = ? LIMIT 1");
      if (!$gst) { echo json_encode(['success'=>false,'error'=>'Server error']); exit(); }
      $gst->bind_param('i', $stock_id);
      $gst->execute();
      $gres = $gst->get_result();
      if ($g = $gres->fetch_assoc()) {
          if ($campus_id && $g['campus_id'] != $campus_id) {
              echo json_encode(['success' => false, 'error' => 'You cannot issue stock from another location.']); exit();
          } elseif ($g['quantity'] < $issue_qty) {
              echo json_encode(['success' => false, 'error' => 'Not enough stock to issue.']); exit();
          } else {
              $upd = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity = quantity - ? WHERE id = ?");
              $upd->bind_param('ii', $issue_qty, $stock_id);
              $upd->execute();
              // fetch new quantity
              $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? LIMIT 1");
              $pst->bind_param('i', $stock_id);
              $pst->execute();
              $new = $pst->get_result()->fetch_assoc();
              echo json_encode(['success' => true, 'message' => "Issued $issue_qty item(s) successfully.", 'item' => $new]);
              exit();
          }
      } else {
          echo json_encode(['success' => false, 'error' => 'Stock record not found.']); exit();
      }
  }
?>
<!DOCTYPE html>
<html lang="en">
    
    <!--Head Code-->
    <?php include("assets/inc/head.php");?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
            <?php include('assets/inc/nav_n.php');?>
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
                                    
                                    <h4 class="page-title">OOU Hospital Management System Dashboard</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <?php if(isset($err)) echo "<div class='alert alert-danger'>$err</div>"; ?>
                        <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

                        <?php if(!empty($picked_item)) { ?>
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">Picked Consumable</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Stock ID:</strong> <?php echo htmlspecialchars($picked_item['stock_id']); ?></p>
                                        <p><strong>Consumable:</strong> <?php echo htmlspecialchars($picked_item['name']); ?> (<?php echo htmlspecialchars($picked_item['category']); ?>)</p>
                                        <p><strong>Available Quantity:</strong> <?php echo (int)$picked_item['quantity']; ?></p>

                                        <form method="post" class="row g-2">
                                            <div class="col-md-4">
                                                <input type="hidden" name="stock_id" value="<?php echo (int)$picked_item['stock_id']; ?>">
                                                <input type="number" name="issue_qty" min="1" class="form-control" placeholder="Qty to issue" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" name="issue_stock" class="btn btn-warning">Issue</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="row">
                            <!--Start OutPatients-->
                            <div class="col-md-6 col-xl-4">
                                <div class="widget-rounded-circle card-box">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                <i class="fab fa-accessible-icon  font-22 avatar-title text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-right">
                                                <?php
                                                    //code for summing up number of out patients 
                                                    $result ="SELECT count(*) FROM individual";
                                                    $stmt = $mysqli->prepare($result);
                                                    $stmt->execute();
                                                    $stmt->bind_result($outpatient);
                                                    $stmt->fetch();
                                                    $stmt->close();
                                                ?>
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $outpatient;?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Individual Patients</p>
                                            </div>
                                        </div>
                                    </div> <!-- end row-->
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                            <!--End Out Patients-->


                            <!--Start InPatients-->
                            <div class="col-md-6 col-xl-4">
                                <div class="widget-rounded-circle card-box">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                 <i class="fab fa-accessible-icon  font-22 avatar-title text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-right">
                                                <?php
                                                    //code for summing up number of in / admitted  patients 
                                                    $result ="SELECT count(*) FROM family_individual";
                                                    $stmt = $mysqli->prepare($result);
                                                    $stmt->execute();
                                                    $stmt->bind_result($inpatient);
                                                    $stmt->fetch();
                                                    $stmt->close();
                                                ?>
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $inpatient;?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Family Patients</p>
                                            </div>
                                        </div>
                                    </div> <!-- end row-->
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                            <!--End InPatients-->

                            <!--Start Employees-->
                            <div class="col-md-6 col-xl-4">
                                <div class="widget-rounded-circle card-box">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                 <i class="fab fa-accessible-icon  font-22 avatar-title text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-right">
                                                <?php
                                                    //code for summing up number of employees in the certain Hospital 
                                                    $result ="SELECT count(*) FROM student ";
                                                    $stmt = $mysqli->prepare($result);
                                                    $stmt->execute();
                                                    $stmt->bind_result($doc);
                                                    $stmt->fetch();
                                                    $stmt->close();
                                                ?>
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $doc;?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Student Patients</p>
                                            </div>
                                        </div>
                                    </div> <!-- end row-->
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                            <!--End Employees-->



                            <!--Start OutPatients-->
                            <div class="col-md-6 col-xl-4">
                                <div class="widget-rounded-circle card-box">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                <i class="fab fa-accessible-icon  font-22 avatar-title text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-right">
                                                <?php
                                                    //code for summing up number of out patients 
                                                    $result ="SELECT count(*) FROM his_patients WHERE pat_type = 'OutPatient' ";
                                                    $stmt = $mysqli->prepare($result);
                                                    $stmt->execute();
                                                    $stmt->bind_result($outpatient);
                                                    $stmt->fetch();
                                                    $stmt->close();
                                                ?>
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $outpatient;?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Ante Natal Patients</p>
                                            </div>
                                        </div>
                                    </div> <!-- end row-->
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                            <!--End Out Patients-->


                            <!--Start InPatients-->
                            <div class="col-md-6 col-xl-4">
                                <div class="widget-rounded-circle card-box">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                <i class="fab fa-accessible-icon  font-22 avatar-title text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-right">
                                                <?php
                                                    //code for summing up number of in / admitted  patients 
                                                    $result ="SELECT count(*) FROM staff ";
                                                    $stmt = $mysqli->prepare($result);
                                                    $stmt->execute();
                                                    $stmt->bind_result($inpatient);
                                                    $stmt->fetch();
                                                    $stmt->close();
                                                ?>
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $inpatient;?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Staff Patients</p>
                                            </div>
                                        </div>
                                    </div> <!-- end row-->
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                            <!--End InPatients-->

                            <!--Start Employees-->
                            <div class="col-md-6 col-xl-4">
                                <div class="widget-rounded-circle card-box">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                 <i class="fas fa-user-tag  font-22 avatar-title text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-right">
                                                <?php
                                                    //code for summing up number of employees in the certain Hospital 
                                                $rdate=date('Y-m-d');
                                                    $result ="SELECT count(*) FROM sendsignal where Date='$rdate'";
                                                    $stmt = $mysqli->prepare($result);
                                                    $stmt->execute();
                                                    $stmt->bind_result($doc);
                                                    $stmt->fetch();
                                                    $stmt->close();
                                                ?>
                                                <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $doc;?></span></h3>
                                                <p class="text-muted mb-1 text-truncate">Todays Visited Patient</p>
                                            </div>
                                        </div>
                                    </div> <!-- end row-->
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                            <!--End Employees-->



                            
                        
                        </div>

                        <div class="row">

                        

                            

                            

                        </div>
                        
                        </div>

                        <div class="row">

                        

                            

                            

                        </div>
                        

                        
                        <!--Recently Employed Employees-->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card-box">
                                    <h4 class="header-title mb-3">Hospital Nursing Staff</h4>

                                    <div class="table-responsive">
                                        <table class="table table-borderless table-hover table-centered m-0">

                                            <thead class="thead-light">
                                                <tr>
                                                    <th colspan="2">Picture</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Department</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <?php
                                                $ret="SELECT * FROM his_docs where doc_dept='Nursing' ORDER BY RAND() LIMIT 10 "; 
                                                //sql code to get to ten docs  randomly
                                                $stmt= $mysqli->prepare($ret) ;
                                                $stmt->execute() ;//ok
                                                $res=$stmt->get_result();
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>
                                            <tbody>
                                                <tr>
                                                    <td style="width: 36px;">
                                                        <img src="../doc/assets/images/users/<?php echo $row->doc_dpic;?>" alt="img" title="contact-img" class="rounded-circle avatar-sm" />
                                                    </td>
                                                    <td>
                                                    </td>
                                                    <td>
                                                        <?php echo $row->doc_fname;?> <?php echo $row->doc_lname;?>
                                                    </td>    
                                                    <td>
                                                        <?php echo $row->doc_email;?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row->doc_dept;?>
                                                    </td>
                                                    <td>
                                                        <a href="his_admin_view_single_employee.php?doc_id=<?php echo $row->doc_id;?>&&doc_number=<?php echo $row->doc_number;?>" class="btn btn-xs btn-primary"><i class="mdi mdi-eye"></i> View</a>
                                                    </td>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
                                                </tr>
                                            </tbody>
                                            <?php }?>
                                        </table>
                                    </div>
                                </div>
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

        <!-- Right Sidebar -->
        <div class="right-bar">
            <div class="rightbar-title">
                <a href="javascript:void(0);" class="right-bar-toggle float-right">
                    <i class="dripicons-cross noti-icon"></i>
                </a>
                <h5 class="m-0 text-white">Settings</h5>
            </div>
            <div class="slimscroll-menu">
                <!-- User box -->
                <div class="user-box">
                    <div class="user-img">
                        <img src="assets/images/users/user-1.jpg" alt="user-img" title="Mat Helme" class="rounded-circle img-fluid">
                        <a href="javascript:void(0);" class="user-edit"><i class="mdi mdi-pencil"></i></a>
                    </div>
            
                    <h5><a href="javascript: void(0);">Geneva Kennedy</a> </h5>
                    <p class="text-muted mb-0"><small>Admin Head</small></p>
                </div>

                <!-- Settings -->
                <hr class="mt-0" />
                <h5 class="pl-3">Basic Settings</h5>
                <hr class="mb-0" />

                <div class="p-3">
                    <div class="checkbox checkbox-primary mb-2">
                        <input id="Rcheckbox1" type="checkbox" checked>
                        <label for="Rcheckbox1">
                            Notifications
                        </label>
                    </div>
                    <div class="checkbox checkbox-primary mb-2">
                        <input id="Rcheckbox2" type="checkbox" checked>
                        <label for="Rcheckbox2">
                            API Access
                        </label>
                    </div>
                    <div class="checkbox checkbox-primary mb-2">
                        <input id="Rcheckbox3" type="checkbox">
                        <label for="Rcheckbox3">
                            Auto Updates
                        </label>
                    </div>
                    <div class="checkbox checkbox-primary mb-2">
                        <input id="Rcheckbox4" type="checkbox" checked>
                        <label for="Rcheckbox4">
                            Online Status
                        </label>
                    </div>
                    <div class="checkbox checkbox-primary mb-0">
                        <input id="Rcheckbox5" type="checkbox" checked>
                        <label for="Rcheckbox5">
                            Auto Payout
                        </label>
                    </div>
                </div>

                <!-- Timeline -->
                <hr class="mt-0" />
                <h5 class="px-3">Messages <span class="float-right badge badge-pill badge-danger">25</span></h5>
                <hr class="mb-0" />
                <div class="p-3">
                    <div class="inbox-widget">
                        <div class="inbox-item">
                            <div class="inbox-item-img"><img src="assets/images/users/user-2.jpg" class="rounded-circle" alt=""></div>
                            <p class="inbox-item-author"><a href="javascript: void(0);" class="text-dark">Tomaslau</a></p>
                            <p class="inbox-item-text">I've finished it! See you so...</p>
                        </div>
                        <div class="inbox-item">
                            <div class="inbox-item-img"><img src="assets/images/users/user-3.jpg" class="rounded-circle" alt=""></div>
                            <p class="inbox-item-author"><a href="javascript: void(0);" class="text-dark">Stillnotdavid</a></p>
                            <p class="inbox-item-text">This theme is awesome!</p>
                        </div>
                        <div class="inbox-item">
                            <div class="inbox-item-img"><img src="assets/images/users/user-4.jpg" class="rounded-circle" alt=""></div>
                            <p class="inbox-item-author"><a href="javascript: void(0);" class="text-dark">Kurafire</a></p>
                            <p class="inbox-item-text">Nice to meet you</p>
                        </div>

                        <div class="inbox-item">
                            <div class="inbox-item-img"><img src="assets/images/users/user-5.jpg" class="rounded-circle" alt=""></div>
                            <p class="inbox-item-author"><a href="javascript: void(0);" class="text-dark">Shahedk</a></p>
                            <p class="inbox-item-text">Hey! there I'm available...</p>
                        </div>
                        <div class="inbox-item">
                            <div class="inbox-item-img"><img src="assets/images/users/user-6.jpg" class="rounded-circle" alt=""></div>
                            <p class="inbox-item-author"><a href="javascript: void(0);" class="text-dark">Adhamdannaway</a></p>
                            <p class="inbox-item-text">This theme is awesome!</p>
                        </div>
                    </div> <!-- end inbox-widget -->
                </div> <!-- end .p-3-->

            </div> <!-- end slimscroll-menu-->
        </div>
        <!-- /Right-bar -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- Vendor js -->
        <script src="assets/js/vendor.min.js"></script>

        <!-- Plugins js-->
        <script src="assets/libs/flatpickr/flatpickr.min.js"></script>
        <script src="assets/libs/jquery-knob/jquery.knob.min.js"></script>
        <script src="assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
        <script src="assets/libs/flot-charts/jquery.flot.js"></script>
        <script src="assets/libs/flot-charts/jquery.flot.time.js"></script>
        <script src="assets/libs/flot-charts/jquery.flot.tooltip.min.js"></script>
        <script src="assets/libs/flot-charts/jquery.flot.selection.js"></script>
        <script src="assets/libs/flot-charts/jquery.flot.crosshair.js"></script>

        <!-- Dashboar 1 init js-->
        <script src="assets/js/pages/dashboard-1.init.js"></script>

        <!-- App js-->
        <script src="assets/js/app.min.js"></script>
        <script>
        jQuery(function($){
            // Intercept pick consumable form in sidebar to call AJAX
            $(document).on('submit', '.pick-consumable-form', function(e){
                e.preventDefault();
                var $form = $(this);
                var stock_id = $form.find('input[name="stock_id"]').val();
                var $btn = $form.find('button[type="submit"]');
                $btn.prop('disabled', true).text('Picking...');
                $.post('nursing_dashboard.php', { ajax: 'pick', stock_id: stock_id }, function(resp){
                    $btn.prop('disabled', false).text('Pick Consumable');
                    if (!resp) { showToast('danger', 'Server error'); return; }
                    if (!resp.success) { showToast('danger', resp.error || 'Error'); return; }
                    // Build picked item card HTML
                    var it = resp.item;
                    var html = '';
                    html += '<div class="row mb-3"><div class="col-12"><div class="card"><div class="card-header bg-info text-white"><h5 class="card-title mb-0">Picked Consumable</h5></div><div class="card-body">';
                    html += '<p><strong>Stock ID:</strong> '+it.stock_id+'</p>';
                    html += '<p><strong>Consumable:</strong> '+it.name+' ('+it.category+')</p>';
                    html += '<p><strong>Available Quantity:</strong> '+it.quantity+'</p>';
                    html += '<form class="row g-2 issue-stock-form">';
                    html += '<div class="col-md-4"><input type="hidden" name="stock_id" value="'+it.stock_id+'"><input type="number" name="issue_qty" min="1" class="form-control" placeholder="Qty to issue" required></div>';
                    html += '<div class="col-md-2"><button type="submit" class="btn btn-warning">Issue</button></div>';
                    html += '</form>';
                    html += '</div></div></div></div>';
                    // Replace existing picked card or insert before the widgets
                    var $existing = $('.picked-consumable-wrapper');
                    if ($existing.length) { $existing.html(html); }
                    else { $('.container-fluid .row').first().before('<div class="picked-consumable-wrapper">'+html+'</div>'); }
                }, 'json').fail(function(){ $btn.prop('disabled', false).text('Pick Consumable'); showToast('danger', 'Request failed'); });
            });

            // Delegate issue form submit (AJAX)
            $(document).on('submit', '.issue-stock-form', function(e){
                e.preventDefault();
                var $form = $(this);
                var stock_id = $form.find('input[name="stock_id"]').val();
                var issue_qty = $form.find('input[name="issue_qty"]').val();
                var $btn = $form.find('button[type="submit"]');
                $btn.prop('disabled', true).text('Issuing...');
                $.post('nursing_dashboard.php', { ajax: 'issue', stock_id: stock_id, issue_qty: issue_qty }, function(resp){
                    $btn.prop('disabled', false).text('Issue');
                    if (!resp) { showToast('danger', 'Server error'); return; }
                    if (!resp.success) { showToast('danger', resp.error || 'Error'); return; }
                    // Update displayed quantity
                    var it = resp.item;
                    var $wrapper = $('.picked-consumable-wrapper');
                    if ($wrapper.length) {
                        $wrapper.find('.card-body').html('<p><strong>Stock ID:</strong> '+it.stock_id+'</p><p><strong>Consumable:</strong> '+it.name+' ('+it.category+')</p><p><strong>Available Quantity:</strong> '+it.quantity+'</p>'+
                            '<form class="row g-2 issue-stock-form"><div class="col-md-4"><input type="hidden" name="stock_id" value="'+it.stock_id+'"><input type="number" name="issue_qty" min="1" class="form-control" placeholder="Qty to issue" required></div><div class="col-md-2"><button type="submit" class="btn btn-warning">Issue</button></div></form>');
                    }
                    showToast('success', resp.message || 'Issued successfully');
                }, 'json').fail(function(){ $btn.prop('disabled', false).text('Issue'); showToast('danger', 'Request failed'); });
            });
        });
        </script>
        
    </body>

</html>