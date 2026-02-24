<!--Server side code to handle  Patient Registration-->
<?php
	session_start();
	include('assets/inc/config.php');
    include('assets/inc/functions.php');

// Ensure new columns exist: `tabs_per_sachet`
// If the column is missing, try to add it so the UI can store per-sachet counts.
$colCheck = $mysqli->query("SHOW COLUMNS FROM drug LIKE 'tabs_per_sachet'");
if (!$colCheck || $colCheck->num_rows == 0) {
    // Attempt to add the column (non-blocking)
    @$mysqli->query("ALTER TABLE drug ADD COLUMN tabs_per_sachet INT DEFAULT 0");
}
// Ensure supplier and lpo ref columns exist
$colCheck2 = $mysqli->query("SHOW COLUMNS FROM drug LIKE 'supplier_name'");
if (!$colCheck2 || $colCheck2->num_rows == 0) {
    @ $mysqli->query("ALTER TABLE drug ADD COLUMN supplier_name VARCHAR(255) DEFAULT NULL");
}
$colCheck3 = $mysqli->query("SHOW COLUMNS FROM drug LIKE 'lpo_ref'");
if (!$colCheck3 || $colCheck3->num_rows == 0) {
    @ $mysqli->query("ALTER TABLE drug ADD COLUMN lpo_ref VARCHAR(100) DEFAULT NULL");
}


/* ======================================================
   DELETE DRUG
====================================================== */
if (isset($_GET['del'])) {
    $id = $_GET['del'];

    // Fetch drug name before deletion (for logs)
    $getName = $mysqli->prepare("SELECT name FROM drug WHERE id=?");
    $getName->bind_param('i', $id);
    $getName->execute();
    $getName->bind_result($drug_name);
    $getName->fetch();
    $getName->close();

    $query = "DELETE FROM drug WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Drug Deleted Successfully";
        log_action($_SESSION['doc_id'], "Deleted drug: $drug_name (ID: $id)");
    } else {
        $err = "Error deleting drug. Please try again.";
    }
    $stmt->close();
}

/* ======================================================
   ADD NEW DRUG
====================================================== */
if (isset($_POST['drug'])) {
    $name = strtoupper(trim($_POST['name']));
    $qnt = trim($_POST['qnt']);
    $amount = trim($_POST['amount']);
    $cate = trim($_POST['cate']);
    $tabs_per_sachet = isset($_POST['tabs_per_sachet']) ? (int) trim($_POST['tabs_per_sachet']) : 0;
    $supplier_name = isset($_POST['supplier_name']) ? trim($_POST['supplier_name']) : null;
    $lpo_ref = isset($_POST['lpo_ref']) ? trim($_POST['lpo_ref']) : null;

    $query = "INSERT INTO drug(name, quantity, amount, category, tabs_per_sachet, supplier_name, lpo_ref) VALUES(?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ssssiss', $name, $qnt, $amount, $cate, $tabs_per_sachet, $supplier_name, $lpo_ref);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Drug Registered Successfully";
        log_action($_SESSION['doc_id'], "Added new drug: $name");
    } else {
        $err = "Error registering drug. Please try again.";
    }
    $stmt->close();
}

/* ======================================================
   CSV UPLOAD SECTION
====================================================== */
if (isset($_POST['upload_csv'])) {
    if (isset($_FILES['drug_file']) && $_FILES['drug_file']['error'] == 0) {
        $file_name = $_FILES['drug_file']['tmp_name'];

        if (($handle = fopen($file_name, "r")) !== FALSE) {
            fgetcsv($handle); // Skip header
            $inserted = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $name = strtoupper(trim($data[0]));
                $qnt = trim($data[1]);
                $amount = trim($data[2]);
                $cate = trim($data[3]);
                $tabs_per_sachet = isset($data[4]) ? (int) trim($data[4]) : 0;
                $supplier_name = isset($data[5]) ? trim($data[5]) : null;
                $lpo_ref = isset($data[6]) ? trim($data[6]) : null;
                $query = "INSERT INTO drug(name, quantity, amount, category, tabs_per_sachet, supplier_name, lpo_ref) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $mysqli->prepare($query);
                if ($stmt) {
                    $stmt->bind_param('ssssiss', $name, $qnt, $amount, $cate, $tabs_per_sachet, $supplier_name, $lpo_ref);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) {
                        $inserted++;
                    }
                    $stmt->close();
                }
            }
            fclose($handle);
            $success = "Imported {$inserted} drugs successfully.";
            log_action($_SESSION['doc_id'], "Imported $inserted drugs from CSV file.");
        } else {
            $err = "Unable to open uploaded file.";
        }
    } else {
        $err = "No file received or upload error.";
    }
}

/* ======================================================
   UPDATE DRUG (AMOUNT or QUANTITY)
====================================================== */
if (isset($_POST['updatedrug_amount'])) {
    $drug_id = (int) $_POST['dname'];
    $camount = trim($_POST['damount']);

    if ($drug_id && $camount !== '') {
        $query = "UPDATE drug SET amount=? WHERE id=?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $camount, $drug_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $success = "Drug amount updated successfully.";
            log_action($_SESSION['doc_id'], "Updated drug amount (ID: $drug_id) to $camount");
        } else {
            $err = "No changes made. Please verify the drug and amount.";
        }
        $stmt->close();
    } else {
        $err = "Invalid input for amount update.";
    }
}

if (isset($_POST['updatedrug_qty'])) {
    // Get and sanitize inputs
    $drug_id = isset($_POST['dname']) ? trim($_POST['dname']) : '';
    $new_qty = isset($_POST['dqty']) ? trim($_POST['dqty']) : '';

    if ($drug_id === '') {
        $err = "Please select a drug to update.";
    } elseif ($new_qty === '') {
        $err = "Please enter a new quantity.";
    } else {
        // Fetch the current quantity (VARCHAR-safe)
        $fetch = $mysqli->prepare("SELECT quantity FROM drug WHERE id = ?");
        if (!$fetch) {
            $err = "Prepare failed: " . $mysqli->error;
        } else {
            $fetch->bind_param('s', $drug_id);
            $fetch->execute();
            $fetch->bind_result($current_qty);
            $found = $fetch->fetch();
            $fetch->close();

            if (!$found) {
                $err = "Drug not found for ID: $drug_id";
            } else {
                $current_qty = trim((string)$current_qty);

                if ($current_qty === $new_qty) {
                    $success = "Quantity is already set to {$new_qty}. No change needed.";
                } else {
                    // Proceed to update
                    $upd = $mysqli->prepare("UPDATE drug SET quantity = ? WHERE id = ?");
                    if (!$upd) {
                        $err = "Prepare failed (update): " . $mysqli->error;
                    } else {
                        $upd->bind_param('ss', $new_qty, $drug_id);
                        $executed = $upd->execute();

                        if ($executed && $upd->affected_rows > 0) {
                            $success = "Drug quantity updated successfully from '{$current_qty}' to '{$new_qty}'.";
                            log_action($_SESSION['doc_id'], "Updated drug quantity (ID: $drug_id) from '{$current_qty}' to '{$new_qty}'");
                        } else {
                            if ($mysqli->error) {
                                $err = "Database error: " . $mysqli->error;
                            } else {
                                $err = "No changes made. Please verify the drug and quantity.";
                            }
                        }
                        $upd->close();
                    }
                }
            }
        }
    }
} // ✅ Properly closed all braces

/* ======================================================
   HELPER FUNCTIONS
====================================================== */
function getdrug($mysqli)
{
    $sql = "SELECT id, name FROM drug ORDER BY id ASC";
    $result = mysqli_query($mysqli, $sql);
    while ($reply = mysqli_fetch_array($result)) {
        $id = (int) $reply['id'];
        $name = htmlspecialchars($reply['name']);
        echo "<option value=\"{$id}\">{$name}</option>";
    }
}
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
            <?php include("assets/inc/sidebar_admin.php");?>
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
                                            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Patients</a></li>
                                            <li class="breadcrumb-item active">Add Patient</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Hospital Drug Registration Form</h4>
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
                                        <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                             <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="inputCity" class="col-form-label"><h3>Drug Name</h3></label>
                                                    <input required="required" type="text" style="color:blue; font-size:medium;"  name="name"placeholder="Enter Drug Name" disable class="form-control" id="inputCity">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputState" class="col-form-label"><h3>Select Drug Category</h3></label>
                                                    <select id="inputState" required="required" name="cate" class="form-control">
                                                        <option>Choose Category</option>
                                                       <option value="Tab">Tab</option>
                                                       <option value="Cream">Cream</option>
                                                       <option value="Syrup">Syrup</option>
                                                       <option value="IV Injection">IV Injection</option>
                                                       <option value="IM Injection">IM Injection</option>
                                                    </select>
                                                </div>
                                                 <div class="form-group col-md-3">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Drug Quantity</h3></label>
                                                    <input type="text" style="color:red;font-size:medium;" required="required" name="qnt" class="form-control" id="inputEmail4" placeholder="Enter Drug Quantity">
                                                </div>

                                                <div class="form-group col-md-3">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Drug Amount</h3></label>
                                                    <input type="text" style="color:red;font-size:medium;" required="required" name="amount" class="form-control" id="inputEmail4" placeholder="Enter Amount">
                                                </div>

                                                <div class="form-group col-md-3">
                                                    <label for="tabsSatchet" class="col-form-label"><h3>Tabs per Satchet</h3></label>
                                                    <input type="number" min="0" step="1" style="color:green;font-size:medium;" name="tabs_per_sachet" class="form-control" id="tabsSatchet" placeholder="e.g. 10">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="supplierName" class="col-form-label"><h3>Supplier Name</h3></label>
                                                    <input type="text" style="color:purple;font-size:medium;" name="supplier_name" class="form-control" id="supplierName" placeholder="Supplier Name (optional)">
                                                </div>

                                                <div class="form-group col-md-3">
                                                    <label for="lpoRef" class="col-form-label"><h3>Reference / LPO No.</h3></label>
                                                    <input type="text" style="color:purple;font-size:medium;" name="lpo_ref" class="form-control" id="lpoRef" placeholder="Ref / LPO (optional)">
                                                </div>
                                                
                                            </div>
                                            
                                            


                                            <button type="submit" name="drug" class="ladda-button btn btn-primary" data-style="expand-right">Register Drug</button>

                                        </form>
                                <hr>
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                                    <div class="form-row">
                                        <div class="col-md-3">
                                            <label><strong>Upload Drug CSV File:</strong></label>
                                            <div class="input-group">
                                                <input type="file" name="drug_file" accept=".csv" class="form-control" required>
                                                <div class="input-group-append">
                                                    <!-- ✅ Added name="upload_csv" -->
                                                    <button type="submit" name="upload_csv" class="btn btn-success btn-sm">Upload</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label><strong>Download CSV Format Template:</strong></label><br>
                                            <a href="drug_csv_handler.php?download_format=1" class="btn btn-outline-primary btn-sm">Download Format</a>
                                        </div>
                                    </div>
                                </form>
                                <hr>

                                     <!-- UPDATE DRUG AMOUNT -->
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label><h3>Select Drug</h3></label>
                                                <select required name="dname" class="form-control">
                                                    <option value="">Choose</option>
                                                    <?php getdrug($mysqli); ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label><h3>Updated Amount</h3></label>
                                                <input required type="text" name="damount" class="form-control" placeholder="Enter Updated Amount">
                                            </div>
                                        </div>
                                        <button type="submit" name="updatedrug_amount" class="btn btn-primary">Update Drug Amount</button>
                                    </form>

                                    <hr>

                                    <!-- UPDATE DRUG QUANTITY -->
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label><h3>Select Drug</h3></label>
                                                <select required name="dname" class="form-control">
                                                    <option value="">Choose</option>
                                                    <?php getdrug($mysqli); ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label><h3>Updated Quantity</h3></label>
                                                <input required type="number" name="dqty" class="form-control" placeholder="Enter Updated Quantity">
                                            </div>
                                        </div>
                                        <button type="submit" name="updatedrug_qty" class="btn btn-primary">Update Drug Quantity</button>
                                    </form>

                                        <!--End Patient Form-->

<div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h4 class="header-title">List of Drug</h4>
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
                                              
                                                <th data-hide="phone" style="color:white;">Drug Name</th>
                                                <th data-hide="phone" style="color:white;">Drug Category</th>
                                                <th data-hide="phone" style="color:white;">Drug Quantity (Tabs)</th>
                                                <th data-hide="phone" style="color:white;">Drug Quantity (Sachets)</th>
                                                <th data-hide="phone" style="color:white;">Supplier</th>
                                                <th data-hide="phone" style="color:white;">Reference / LPO</th>
                                                <th data-hide="phone" style="color:white;">Drug Amount</th>
                                                
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                $ret="SELECT * FROM  drug ORDER BY id ASC "; 
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
                                                    <td><?php echo $row->name;?></td>
                                                    <td><?php echo $row->category;?></td>
                                                    <?php
                                                        $qty_tabs = (int) $row->quantity;
                                                        $tabs_per = isset($row->tabs_per_sachet) ? (int) $row->tabs_per_sachet : 0;
                                                        $qty_sachets = ($tabs_per > 0) ? floor($qty_tabs / $tabs_per) : 0;
                                                    ?>
                                                    <td><?php echo $qty_tabs;?> Tabs</td>
                                                    <td><?php echo $qty_sachets;?> Sachets<?php echo ($tabs_per>0)?" (1 satchet={$tabs_per} tabs)":"";?></td>
                                                    <td><?php echo htmlspecialchars($row->supplier_name);?></td>
                                                    <td><?php echo htmlspecialchars($row->lpo_ref);?></td>
                                                    <td><?php echo $row->amount;?></td>
                                                    

                                                    <td><a href="setup_drug.php?code=<?php echo $row->id;?>" class=""><img src="assets/images/ok.png" height="20" width="20"></a><a href="setup_drug.php?del=<?php echo $row->id;?>" class=""><img src="assets/images/del.png" height="20" width="20"></a></td>
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