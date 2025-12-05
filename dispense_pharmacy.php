<?php
// dispense_pharmacy.php  (updated — multi-campus ready, safe queries, fixes)
// Paste / replace your existing file with this content.

session_start();
include('assets/inc/config.php');
include('assets/inc/functions.php');

/* ============================
   DELETE DRUG (store)
   URL: ?del=<drug_id>
   ============================ */
if (isset($_GET['del'])) {
    $id = (int) $_GET['del'];
    if ($id > 0) {
        $query = "DELETE FROM drug WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $success = "Drug deleted successfully.";
            // Log action if function exists
            if (function_exists('log_action') && isset($_SESSION['user_id'])) {
                log_action($_SESSION['user_id'], "Deleted drug record with ID: $id");
            }
        } else {
            $err = "Could not delete drug. Try again later.";
        }
        $stmt->close();
    } else {
        $err = "Invalid drug ID.";
    }
}

/* ============================
   DELETE PHARMACY ROW
   URL: ?del_pharmacy=<pharmacy_id>
   ============================ */
if (isset($_GET['del_pharmacy'])) {
    $pid = (int) $_GET['del_pharmacy'];
    if ($pid > 0) {
        $q = "DELETE FROM pharmacy WHERE id = ?";
        $s = $mysqli->prepare($q);
        $s->bind_param('i', $pid);
        if ($s->execute()) {
            $success = "Pharmacy item removed.";
            if (function_exists('log_action') && isset($_SESSION['user_id'])) {
                log_action($_SESSION['user_id'], "Deleted pharmacy record ID: $pid");
            }
        } else {
            $err = "Could not remove pharmacy item.";
        }
        $s->close();
    } else {
        $err = "Invalid pharmacy ID.";
    }
}

/* ============================
   Helper functions (fixed to accept $mysqli)
   ============================ */
function getscans($mysqli){
    $sql = "SELECT * FROM scan ORDER BY id DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($reply = $res->fetch_assoc()) {
        echo "<option value=\"" . htmlspecialchars($reply['name']) . "\">" . htmlspecialchars($reply['name']) . "</option>";
    }
    $stmt->close();
}

function getscan($mysqli){
    $sql = "SELECT * FROM scan ORDER BY id ASC";
    $result = mysqli_query($mysqli, $sql);
    while ($reply = mysqli_fetch_array($result)) {
        echo "<option value=\"" . htmlspecialchars($reply['name']) . "\">" . htmlspecialchars($reply['name']) . "</option>";
    }
}

/* ============================
   Fetch store drugs
   (support either 'qty' or 'quantity' column)
   ============================ */
$store_sql = "SELECT id, name, COALESCE(quantity, 0) AS store_quantity, COALESCE(amount, 0) AS amount, COALESCE(category, '') AS category FROM drug ORDER BY id ASC";
$store_stmt = $mysqli->prepare($store_sql);
$store_stmt->execute();
$store_res = $store_stmt->get_result();

/* ============================
   Fetch pharmacy rows with location name (if pharmacy_location table exists)
   ============================ */
$pharmacy_sql = "
    SELECT p.id, p.name, COALESCE(p.quantity, 0) AS quantity, COALESCE(p.amount, 0) AS amount, COALESCE(p.category,'') AS category,
           COALESCE(pl.name, 'Main Pharmacy') AS location_name, COALESCE(p.pharmacy_location_id, 1) as pharmacy_location_id
    FROM pharmacy p
    LEFT JOIN pharmacy_location pl ON p.pharmacy_location_id = pl.id
    ORDER BY p.id ASC
";
$pharmacy_stmt = $mysqli->prepare($pharmacy_sql);
$pharmacy_stmt->execute();
$pharmacy_res = $pharmacy_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
    <?php include('assets/inc/head.php'); ?>
    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
            <?php include("assets/inc/nav_r.php");?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
            <?php include("assets/inc/sidebar_admin.php");?>
            <!-- Left Sidebar End -->

            <div class="content-page">
                <div class="content">
                    <div class="container-fluid">

                        <!-- Page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Pharmacy</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">DRUG MOVEMENT TO PHARMACY FROM STORE</h4>
                                </div>
                            </div>
                        </div>

                        <!-- STORE DRUG LIST -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                  <div class="card-body">
                                    <h4 class="header-title"><strong>DRUG MOVEMENT FROM STORE</strong></h4>

                                    <div class="mb-2">
                                      <div class="row">
                                        <div class="col-12 text-sm-center form-inline">
                                          <div class="form-group">
                                            <input id="demo-foo-search" type="text" placeholder="Search" class="form-control form-control-sm" autocomplete="on">
                                          </div>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="table-responsive">
                                      <table id="store-drug-table" style="background-color:grey;" class="datatable-1 table table-bordered table-striped display" data-page-size="7">
                                        <thead>
                                          <tr>
                                            <th style="color:white;">S/N</th>
                                            <th data-hide="phone" style="color:white;">Drug Name</th>
                                            <th data-hide="phone" style="color:white;">Drug Quantity</th>
                                            <th data-hide="phone" style="color:white;">Drug Amount</th>
                                            <th data-hide="phone" style="color:white;">Drug Category</th>
                                            <th data-hide="phone" style="color:white;">Action</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          <?php
                                          $cnt = 1;
                                          while ($row = $store_res->fetch_assoc()) {
                                          ?>
                                            <tr>
                                              <td><?= htmlspecialchars($cnt) ?></td>
                                              <td><?= htmlspecialchars($row['name']) ?></td>
                                              <td><?= htmlspecialchars($row['store_quantity']) ?></td>
                                              <td><?= htmlspecialchars($row['amount']) ?></td>
                                              <td><?= htmlspecialchars($row['category']) ?></td>

                                              <!-- Link to dispense_pharmacy2.php where user selects campus -->
                                              <td>
                                                <a href="dispense_pharmacy2.php?id=<?= urlencode($row['id']) ?>&name=<?= urlencode($row['name']) ?>&qnt=<?= urlencode($row['store_quantity']) ?>&amnt=<?= urlencode($row['amount']) ?>&cate=<?= urlencode($row['category']) ?>" class="btn btn-success">
                                                  Dispense To Pharmacy <img src="assets/images/ok.png" height="20" width="20" alt="">
                                                </a>

                                                <!-- Optional: delete drug from store -->
                                                <a href="?del=<?= (int)$row['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this drug from store?');">Delete</a>
                                              </td>
                                            </tr>
                                          <?php
                                            $cnt++;
                                          }
                                          ?>
                                        </tbody>
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
                                  </div> <!-- end card-body -->
                                </div> <!-- end card -->
                            </div>
                        </div>

                        <!-- PHARMACY DRUG LIST (shows pharmacy rows with campus) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                  <div class="card-body">
                                    <h4 class="header-title"><strong>PHARMACY DRUGS</strong></h4>

                                    <div class="table-responsive">
                                      <table id="pharmacy-table" style="background-color:grey;" class="datatable-1 table table-bordered table-striped display" data-page-size="7">
                                        <thead>
                                          <tr>
                                            <th style="color:white;">S/N</th>
                                            <th style="color:white;">Drug Name</th>
                                            <th style="color:white;">Drug Quantity</th>
                                            <th style="color:white;">Drug Category</th>
                                            <th style="color:white;">Campus</th>
                                            <th style="color:white;">Action</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          <?php
                                            $cnt2 = 1;
                                            while ($prow = $pharmacy_res->fetch_assoc()) {
                                          ?>
                                            <tr>
                                              <td><?= htmlspecialchars($cnt2) ?></td>
                                              <td><?= htmlspecialchars($prow['name']) ?></td>
                                              <td><?= htmlspecialchars($prow['quantity']) ?></td>
                                              <td><?= htmlspecialchars($prow['category']) ?></td>
                                              <td><?= htmlspecialchars($prow['location_name']) ?></td>
                                              <td>
                                                <!-- remove from pharmacy -->
                                                <a href="?del_pharmacy=<?= (int)$prow['id'] ?>" class="btn btn-danger" onclick="return confirm('Remove this item from pharmacy?');">
                                                  Remove <img src="assets/img/remove.png" height="20" width="20" alt="">
                                                </a>
                                              </td>
                                            </tr>
                                          <?php
                                            $cnt2++;
                                            }
                                          ?>
                                        </tbody>
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
                                  </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- container -->
                </div> <!-- content -->

                <!-- Footer Start -->
                <?php include('assets/inc/footer.php');?>
                <!-- end Footer -->
            </div>
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

        <!-- Footable (if used elsewhere) -->
        <script src="assets/libs/footable/footable.all.min.js"></script>
        <script src="assets/js/pages/foo-tables.init.js"></script>

    </body>
</html>