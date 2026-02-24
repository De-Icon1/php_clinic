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

  
  /* ============================================================
     WORKING LOCATION HANDLING (SHARED WITH LAB CONSUMABLES MODEL)
  =============================================================== */
  if (isset($_POST['set_location'])) {
      $wl = $_POST['working_location'];
      if (is_numeric($wl)) {
          $id = intval($wl);
          $stmt = $mysqli->prepare("SELECT name FROM campus_locations WHERE id = ? LIMIT 1");
          if ($stmt) {
              $stmt->bind_param('i', $id);
              $stmt->execute();
              $r = $stmt->get_result();
              if ($row = $r->fetch_assoc()) {
                  $_SESSION['working_location_id'] = $id;
                  $_SESSION['working_location'] = $row['name'];
              } else {
                  $_SESSION['working_location_id'] = $id;
                  $_SESSION['working_location'] = '';
              }
          }
      } else {
          $_SESSION['working_location'] = $wl;
          $stmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
          if ($stmt) {
              $stmt->bind_param('s', $wl);
              $stmt->execute();
              $r = $stmt->get_result();
              if ($row = $r->fetch_assoc()) {
                  $_SESSION['working_location_id'] = intval($row['id']);
              }
          }
      }
      // Preserve any existing query parameters (like nurse_pick=1) on redirect
      $qs = $_GET;
      if (isset($qs['clear_location'])) {
          unset($qs['clear_location']);
      }
      $query = http_build_query($qs);
      $target = $_SERVER['PHP_SELF'] . ($query ? ('?' . $query) : '');
      header('Location: ' . $target);
      exit;
  }

  if (isset($_GET['clear_location'])) {
      unset($_SESSION['working_location']);
      unset($_SESSION['working_location_id']);
      // Preserve other query parameters (like nurse_pick=1) when clearing location
      $qs = $_GET;
      unset($qs['clear_location']);
      $query = http_build_query($qs);
      $target = $_SERVER['PHP_SELF'] . ($query ? ('?' . $query) : '');
      header('Location: ' . $target);
      exit;
  }
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
      if ($campus_id) {
          $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? AND s.campus_id = ? LIMIT 1");
          if ($pst) {
              $pst->bind_param('ii', $stock_id, $campus_id);
              $pst->execute();
              $pres = $pst->get_result();
              if ($row = $pres->fetch_assoc()) {
                  $picked_item = $row;
              } else {
                  $err = "Consumable stock ID not found for your location.";
              }
          }
      } else {
          $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? LIMIT 1");
          if ($pst) {
              $pst->bind_param('i', $stock_id);
              $pst->execute();
              $pres = $pst->get_result();
              if ($row = $pres->fetch_assoc()) {
                  $picked_item = $row;
              } else {
                  $err = "Consumable stock ID not found.";
              }
          }
      }
  }

  // AJAX: pick consumable (returns JSON)
  if (isset($_POST['ajax']) && $_POST['ajax'] === 'pick') {
      header('Content-Type: application/json');
      $stock_id = (int) ($_POST['stock_id'] ?? 0);
      if ($campus_id) {
          $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? AND s.campus_id = ? LIMIT 1");
          if ($pst) {
              $pst->bind_param('ii', $stock_id, $campus_id);
              $pst->execute();
              $pres = $pst->get_result();
              if ($row = $pres->fetch_assoc()) {
                  echo json_encode(['success' => true, 'item' => $row]);
              } else {
                  echo json_encode(['success' => false, 'error' => 'Consumable stock ID not found for your location.']);
              }
          } else {
              echo json_encode(['success' => false, 'error' => 'Server error preparing statement.']);
          }
      } else {
          $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? LIMIT 1");
          if ($pst) {
              $pst->bind_param('i', $stock_id);
              $pst->execute();
              $pres = $pst->get_result();
              if ($row = $pres->fetch_assoc()) {
                  echo json_encode(['success' => true, 'item' => $row]);
              } else {
                  echo json_encode(['success' => false, 'error' => 'Consumable stock ID not found.']);
              }
          } else {
              echo json_encode(['success' => false, 'error' => 'Server error preparing statement.']);
          }
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
          if ($campus_id) {
              $gst = $mysqli->prepare("SELECT quantity, campus_id FROM nurse_consumable_stock WHERE id = ? AND campus_id = ? LIMIT 1");
              $gst->bind_param('ii', $stock_id, $campus_id);
          } else {
              $gst = $mysqli->prepare("SELECT quantity, campus_id FROM nurse_consumable_stock WHERE id = ? LIMIT 1");
              $gst->bind_param('i', $stock_id);
          }
          $gst->execute();
          $gres = $gst->get_result();
          if ($g = $gres->fetch_assoc()) {
              if ($g['quantity'] < $issue_qty) {
                  $err = "Not enough stock to issue.";
              } else {
                  $upd = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity = quantity - ? WHERE id = ?");
                  $upd->bind_param('ii', $issue_qty, $stock_id);
                  $upd->execute();
                  if ($upd) $success = "Issued $issue_qty item(s) successfully.";
                  // Refresh picked item for display (respect campus)
                  if ($campus_id) {
                      $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? AND s.campus_id = ? LIMIT 1");
                      $pst->bind_param('ii', $stock_id, $campus_id);
                  } else {
                      $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? LIMIT 1");
                      $pst->bind_param('i', $stock_id);
                  }
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
      if ($campus_id) {
          $gst = $mysqli->prepare("SELECT quantity, campus_id FROM nurse_consumable_stock WHERE id = ? AND campus_id = ? LIMIT 1");
          if (!$gst) { echo json_encode(['success'=>false,'error'=>'Server error']); exit(); }
          $gst->bind_param('ii', $stock_id, $campus_id);
      } else {
          $gst = $mysqli->prepare("SELECT quantity, campus_id FROM nurse_consumable_stock WHERE id = ? LIMIT 1");
          if (!$gst) { echo json_encode(['success'=>false,'error'=>'Server error']); exit(); }
          $gst->bind_param('i', $stock_id);
      }
      $gst->execute();
      $gres = $gst->get_result();
      if ($g = $gres->fetch_assoc()) {
          if ($g['quantity'] < $issue_qty) {
              echo json_encode(['success' => false, 'error' => 'Not enough stock to issue.']); exit();
          } else {
              $upd = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity = quantity - ? WHERE id = ?");
              $upd->bind_param('ii', $issue_qty, $stock_id);
              $upd->execute();
              // fetch new quantity
              if ($campus_id) {
                  $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? AND s.campus_id = ? LIMIT 1");
                  $pst->bind_param('ii', $stock_id, $campus_id);
              } else {
                  $pst = $mysqli->prepare("SELECT s.id AS stock_id, s.quantity, s.campus_id, n.id AS consumable_id, n.name, n.category FROM nurse_consumable_stock s JOIN nurse_consumables n ON n.id = s.consumable_id WHERE s.id = ? LIMIT 1");
                  $pst->bind_param('i', $stock_id);
              }
              $pst->execute();
              $new = $pst->get_result()->fetch_assoc();
              echo json_encode(['success' => true, 'message' => "Issued $issue_qty item(s) successfully.", 'item' => $new]);
              exit();
          }
      } else {
          echo json_encode(['success' => false, 'error' => 'Stock record not found.']); exit();
      }
  }

  /* ============================================================
     AJAX: FETCH NURSE CONSUMABLES BASED ON WORKING LOCATION
     (MODELED AFTER consumables_ajax.php / lab consumables)
  =============================================================== */
  if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
      header('Content-Type: application/json');

      if ((!isset($_SESSION['working_location']) || empty($_SESSION['working_location'])) &&
          (!isset($_SESSION['working_location_id']) || empty($_SESSION['working_location_id']))) {
          echo json_encode(['status' => 'error', 'message' => 'Working location not set.']);
          exit;
      }

      $use_id = false;
      $location_id = null;
      if (isset($_SESSION['working_location_id']) && intval($_SESSION['working_location_id']) > 0) {
          $use_id = true;
          $location_id = intval($_SESSION['working_location_id']);
      } else {
          $location = $_SESSION['working_location'];
      }

      if ($use_id) {
          $query = "SELECT 
                        s.id,
                        n.name AS consumable_name,
                        n.category,
                        s.quantity,
                        cl.name AS location
                    FROM nurse_consumable_stock s
                    JOIN nurse_consumables n ON n.id = s.consumable_id
                    JOIN campus_locations cl ON cl.id = s.campus_id
                    WHERE s.campus_id = ?
                    ORDER BY n.name ASC";

          $stmtAjax = $mysqli->prepare($query);
          $stmtAjax->bind_param('i', $location_id);
          $stmtAjax->execute();
      } else {
          $query = "SELECT 
                        s.id,
                        n.name AS consumable_name,
                        n.category,
                        s.quantity,
                        cl.name AS location
                    FROM nurse_consumable_stock s
                    JOIN nurse_consumables n ON n.id = s.consumable_id
                    JOIN campus_locations cl ON cl.id = s.campus_id
                    WHERE s.campus_id = (
                          SELECT id FROM campus_locations WHERE name = ?
                    )
                    ORDER BY n.name ASC";

          $stmtAjax = $mysqli->prepare($query);
          $stmtAjax->bind_param('s', $location);
          $stmtAjax->execute();
      }

      $resultAjax = $stmtAjax->get_result();
      $consumables = [];
      while ($row = $resultAjax->fetch_assoc()) {
          $consumables[] = $row;
      }

      echo json_encode(['status' => 'success', 'data' => $consumables]);
      exit;
  }

  /* ============================================================
     AJAX: PICK NURSE CONSUMABLE — DEDUCT FROM nurse_consumable_stock
     Using the same inline quantity model as consumables_ajax.php
  =============================================================== */
  if (isset($_POST['pick']) && isset($_POST['id']) && isset($_POST['qty'])) {
      header('Content-Type: application/json');

      if ((!isset($_SESSION['working_location']) || empty($_SESSION['working_location'])) &&
          (!isset($_SESSION['working_location_id']) || empty($_SESSION['working_location_id']))) {
          echo json_encode(['status' => 'error', 'message' => 'Working location not set.']);
          exit;
      }

      $id  = intval($_POST['id']);
      $qty = intval($_POST['qty']);

      if ($qty <= 0) {
          echo json_encode(['status' => 'error', 'message' => 'Invalid quantity.']);
          exit;
      }

      // Resolve campus/location id
      $campus_pick_id = null;
      if (isset($_SESSION['working_location_id']) && intval($_SESSION['working_location_id']) > 0) {
          $campus_pick_id = intval($_SESSION['working_location_id']);
      } else {
          $locationName = $_SESSION['working_location'];
          $locStmt = $mysqli->prepare("SELECT id FROM campus_locations WHERE name = ? LIMIT 1");
          if ($locStmt) {
              $locStmt->bind_param('s', $locationName);
              $locStmt->execute();
              $locRes = $locStmt->get_result();
              if ($locRow = $locRes->fetch_assoc()) {
                  $campus_pick_id = intval($locRow['id']);
              }
          }
      }

      if (!$campus_pick_id) {
          echo json_encode(['status' => 'error', 'message' => 'Unable to resolve working location.']);
          exit;
      }

      $mysqli->begin_transaction();
      try {
          $stmtCheck = $mysqli->prepare("SELECT quantity FROM nurse_consumable_stock WHERE id = ? AND campus_id = ? LIMIT 1");
          $stmtCheck->bind_param('ii', $id, $campus_pick_id);
          $stmtCheck->execute();
          $stockResult = $stmtCheck->get_result();

          if ($stockResult->num_rows === 0) {
              throw new Exception('Consumable not found for your current location.');
          }

          $stockRow = $stockResult->fetch_assoc();
          $stockQty = (int)$stockRow['quantity'];

          if ($qty > $stockQty) {
              throw new Exception('Quantity exceeds available stock at this location.');
          }

          $newQty = $stockQty - $qty;
          $stmtUpd = $mysqli->prepare("UPDATE nurse_consumable_stock SET quantity = ? WHERE id = ? AND campus_id = ?");
          $stmtUpd->bind_param('iii', $newQty, $id, $campus_pick_id);
          $stmtUpd->execute();

          $mysqli->commit();
          echo json_encode(['status' => 'success', 'message' => 'Consumable picked successfully.']);
      } catch (Exception $e) {
          $mysqli->rollback();
          echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
      }

      exit;
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
                                    <h2><?php echo getcampus($campusid,$mysqli); ?></h2>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        <?php if(isset($err)) echo "<div class='alert alert-danger'>$err</div>"; ?>
                        <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

                        <?php if (isset($_GET['nurse_pick']) && $_GET['nurse_pick'] == '1'): ?>

                        <!-- Nurse Consumables Picking (modeled after consumables_ajax.php) -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="mb-3">Nurse Consumables (Pick Items)</h4>

                                        <?php if (!isset($_SESSION['working_location']) && !isset($_SESSION['working_location_id'])): ?>
                                            <form method="post" class="mb-3">
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-4">
                                                        <select name="working_location" class="form-control" required>
                                                            <option value="">-- Select Your Working Location --</option>
                                                            <?php
                                                            $campuses_nd = $mysqli->query("SELECT id, name FROM campus_locations ORDER BY name ASC");
                                                            while($c = $campuses_nd->fetch_assoc()){
                                                                echo "<option value='".intval($c['id'])."'>".htmlspecialchars($c['name'])."</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-md-2">
                                                        <button type="submit" name="set_location" class="btn btn-primary w-100">Set Location</button>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <p>
                                                <strong>Current Working Location:</strong> <?php echo htmlspecialchars($_SESSION['working_location']); ?>
                                                <a href="?clear_location=1&amp;nurse_pick=1" class="btn btn-sm btn-warning ms-2">Change Location</a>
                                            </p>

                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="nurse_consumable_table">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Consumable</th>
                                                            <th>Category</th>
                                                            <th>Quantity</th>
                                                            <th>Location</th>
                                                            <th>Pick Quantity</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="nurse_consumable_results">
                                                        <tr><td colspan="5" class="text-center text-muted">Loading consumables...</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['working_location']) || isset($_SESSION['working_location_id'])): ?>
                        <script>
                        async function fetchNurseConsumables() {
                            const res = await fetch('nursing_dashboard.php?ajax=1');
                            const data = await res.json();
                            const tbody = document.getElementById('nurse_consumable_results');
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            if (data.status === 'success') {
                                if (!data.data || data.data.length === 0) {
                                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No consumables found for this location</td></tr>';
                                    return;
                                }

                                data.data.forEach(row => {
                                    tbody.innerHTML += `
                                        <tr>
                                            <td>${row.consumable_name}</td>
                                            <td>${row.category}</td>
                                            <td>${row.quantity}</td>
                                            <td>${row.location}</td>
                                            <td>
                                                <input type="number" min="1" max="${row.quantity}" value="1"
                                                       class="form-control form-control-sm d-inline-block"
                                                       style="width:80px" id="nurse_pick_qty_${row.id}">
                                                <button type="button" class="btn btn-sm btn-success ms-1" onclick="pickNurseConsumable(${row.id}, ${row.quantity})">Pick</button>
                                            </td>
                                        </tr>
                                    `;
                                });
                            } else {
                                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">'+(data.message || 'Error loading consumables')+'</td></tr>';
                            }
                        }

                        async function pickNurseConsumable(id, maxQty) {
                            const input = document.getElementById('nurse_pick_qty_' + id);
                            let qty = parseInt(input.value, 10);

                            if (isNaN(qty) || qty <= 0) {
                                alert('Please enter a valid quantity.');
                                return;
                            }

                            if (qty > maxQty) {
                                alert('You cannot pick more than the available quantity (' + maxQty + ').');
                                return;
                            }

                            const formData = new FormData();
                            formData.append('id', id);
                            formData.append('qty', qty);
                            formData.append('pick', 1);

                            const res = await fetch('nursing_dashboard.php', {
                                method: 'POST',
                                body: formData
                            });
                            const data = await res.json();
                            alert(data.message || (data.status === 'success' ? 'Operation completed.' : 'Error'));
                            fetchNurseConsumables();
                        }

                        document.addEventListener('DOMContentLoaded', fetchNurseConsumables);
                        </script>
                        <?php endif; ?>

                        <?php endif; ?>

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