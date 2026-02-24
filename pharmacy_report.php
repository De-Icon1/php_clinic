<?php
session_start();
include('assets/inc/config.php');
// campus scoping (session) — allow per-report override via POST `report_campus_id`
$report_campus = isset($_POST['report_campus_id']) ? (int)$_POST['report_campus_id'] : null;
$campus_id = $report_campus ? $report_campus : (isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : null);
function table_has_campus($mysqli, $table)
{
    $t = $mysqli->real_escape_string($table);
    $q = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $t . "' AND COLUMN_NAME='campus_id'";
    $r = $mysqli->query($q);
    if ($r) {
        $row = $r->fetch_assoc();
        return (int) $row['cnt'] > 0;
    }
    return false;
}
$store_has_campus = table_has_campus($mysqli, 'store_stock');

// ==========================
// 🔹 1. DELETE DRUG RECORD
// ==========================
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $query = "DELETE FROM drug WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Drug Deleted Successfully";
    } else {
        $err = "Please Try Again Later";
    }
    $stmt->close();
}


// ==========================
// 🔹 2. GENERATE REPORT
// ==========================
if (isset($_POST['genreport']) || isset($_POST['export_pdf']) || isset($_POST['export_excel'])) {

    $datefrm = $_POST['datefrom'];
    $dateto = $_POST['dateto'];

    // Sanity check
    if (empty($datefrm) || empty($dateto)) {
        $err = "Please select both start and end dates.";
    } else {
        // Get report data (apply campus filter if available)
        if ($store_has_campus && $campus_id) {
            $ret = "SELECT * FROM store_stock WHERE date BETWEEN ? AND ? AND campus_id = ? ORDER BY date ASC, name ASC";
            $stmt = $mysqli->prepare($ret);
            $stmt->bind_param('ssi', $datefrm, $dateto, $campus_id);
        } else {
            $ret = "SELECT * FROM store_stock WHERE date BETWEEN ? AND ? ORDER BY date ASC, name ASC";
            $stmt = $mysqli->prepare($ret);
            $stmt->bind_param('ss', $datefrm, $dateto);
        }
        $stmt->execute();
        $res = $stmt->get_result();

        $reportData = [];
        while ($row = $res->fetch_assoc()) {
            $row['stockout'] = ($row['addstock'] + $row['opening']) - $row['closing'];
            $reportData[] = $row;
        }

        // ---------- EXPORT TO PDF ----------
        if (isset($_POST['export_pdf'])) {
            require('fpdf/fpdf.php');
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, "Hospital Periodic Store Report", 0, 1, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 10, "From: $datefrm To: $dateto", 0, 1, 'C');
            $pdf->Ln(5);

            // Table Header
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(10, 8, "S/N", 1);
            $pdf->Cell(25, 8, "Date", 1);
            $pdf->Cell(50, 8, "Drug Name", 1);
            $pdf->Cell(20, 8, "Opening", 1);
            $pdf->Cell(20, 8, "Added", 1);
            $pdf->Cell(20, 8, "Stock Out", 1);
            $pdf->Cell(20, 8, "Closing", 1);
            $pdf->Ln();

            // Table Rows
            $pdf->SetFont('Arial', '', 9);
            $sn = 1;
            foreach ($reportData as $r) {
                $pdf->Cell(10, 8, $sn++, 1);
                $pdf->Cell(25, 8, $r['date'], 1);
                $pdf->Cell(50, 8, $r['name'], 1);
                $pdf->Cell(20, 8, $r['opening'], 1);
                $pdf->Cell(20, 8, $r['addstock'], 1);
                $pdf->Cell(20, 8, $r['stockout'], 1);
                $pdf->Cell(20, 8, $r['closing'], 1);
                $pdf->Ln();
            }

            $pdf->Output('D', "Periodic_Report_{$datefrm}_to_{$dateto}.pdf");
            exit();
        }

        // ---------- EXPORT TO EXCEL ----------
        if (isset($_POST['export_excel'])) {
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=Periodic_Report_{$datefrm}_to_{$dateto}.csv");
            $output = fopen("php://output", "w");

            fputcsv($output, ['S/N', 'Date', 'Drug Name', 'Opening', 'Added', 'Stock Out', 'Closing']);
            $sn = 1;
            foreach ($reportData as $r) {
                fputcsv($output, [$sn++, $r['date'], $r['name'], $r['opening'], $r['addstock'], $r['stockout'], $r['closing']]);
            }

            fclose($output);
            exit();
        }
    }
}


// ==========================
// 🔹 3. CLOSING STOCK UPDATE
// ==========================
function pharmacyclosingstock($date, $mysqli)
{
    $sql = "SELECT COUNT(*) AS cnt FROM pharmacy_stock WHERE date=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res['cnt'] > 0) {
        $result = $mysqli->query("SELECT name, quantity FROM pharmacy ORDER BY id ASC");
        while ($row = $result->fetch_assoc()) {
            $name = $row['name'];
            $qty = $row['quantity'];
            $update = $mysqli->prepare("UPDATE pharmacy_stock SET closing=? WHERE name=? AND date=?");
            $update->bind_param('iss', $qty, $name, $date);
            $update->execute();
        }
    }
}

function storeclosingstock($date, $mysqli)
{
    $sql = "SELECT COUNT(*) AS cnt FROM store_stock WHERE date=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res['cnt'] > 0) {
        $result = $mysqli->query("SELECT name, quantity FROM drug ORDER BY id ASC");
        while ($row = $result->fetch_assoc()) {
            $name = $row['name'];
            $qty = $row['quantity'];
            $update = $mysqli->prepare("UPDATE store_stock SET closing=? WHERE name=? AND date=?");
            $update->bind_param('iss', $qty, $name, $date);
            $update->execute();
        }
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

            <button type="submit" name="genreport" class="ladda-button btn btn-primary">Process Report</button>

            <div class="mt-3">
                <button type="submit" name="genreport" class="btn btn-info">View Report</button>
                <button type="submit" name="export_pdf" class="btn btn-danger">Export as PDF</button>
                <button type="submit" name="export_excel" class="btn btn-success">Export as Excel</button>
            </div>


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
                                    <h4 class="page-title">Drug Dispensed to Pharmacy</h4>
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
                                        <?php
                                        // campus selector for pharmacy report
                                        if (isset($mysqli)) {
                                            $campuses = [];
                                            $candidateTables = ['campus_locations','campuses','locations','his_campus'];
                                            foreach ($candidateTables as $ct) {
                                                $q = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $mysqli->real_escape_string($ct) . "'";
                                                $r = $mysqli->query($q);
                                                if ($r && (int)$r->fetch_assoc()['cnt'] > 0) {
                                                    $cols = [];
                                                    $colRes = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $mysqli->real_escape_string($ct) . "'");
                                                    if ($colRes) { while ($c = $colRes->fetch_assoc()) { $cols[] = $c['COLUMN_NAME']; } }
                                                    $labelCols = ['name','title','campus_name','campus','location','site'];
                                                    $label = null; foreach ($labelCols as $lc) { if (in_array($lc,$cols)) { $label=$lc; break; } }
                                                    if ($label) { $safeLabel = $mysqli->real_escape_string($label); $res = $mysqli->query("SELECT id, `".$safeLabel."` AS name FROM " . $ct . " ORDER BY `".$safeLabel."` ASC"); }
                                                    else { $res = $mysqli->query("SELECT id FROM " . $ct); }
                                                    if ($res) { while ($row=$res->fetch_assoc()) { $campuses[] = ['id'=>$row['id'],'name'=> isset($row['name'])? $row['name']: 'Campus '. $row['id'] ]; } }
                                                    break;
                                                }
                                            }
                                            if (empty($campuses)) {
                                                $seen=[]; $tables=['pharmacy','store_stock','pharmacy_stock'];
                                                foreach($tables as $t){ $q = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" . $mysqli->real_escape_string($t) . "' AND COLUMN_NAME='campus_id'"; $r=$mysqli->query($q); if($r && (int)$r->fetch_assoc()['cnt']>0){ $res=$mysqli->query("SELECT DISTINCT campus_id FROM " . $t . " WHERE campus_id IS NOT NULL ORDER BY campus_id ASC"); if($res){ while($row=$res->fetch_assoc()){ $id=(int)$row['campus_id']; if($id && !in_array($id,$seen)){ $seen[]=$id; $campuses[]=['id'=>$id,'name'=>'Campus '.$id]; } } } } }
                                            }
                                            if (!empty($campuses)){
                                                $sel = isset($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : 0;
                                                echo '<div style="margin-bottom:10px;"><label>Campus</label><select id="campus_select" class="form-control" style="max-width:240px;">';
                                                echo '<option value="0"'.($sel===0?' selected':'').'>All campuses</option>';
                                                foreach($campuses as $c){ $s = ($sel===(int)$c['id'])? ' selected': ''; echo '<option value="'.htmlspecialchars($c['id']).'"'.$s.'>'.htmlspecialchars($c['name']).'</option>'; }
                                                echo '</select></div>';
                                                ?>
                                                <script>
                                                (function(){var sel=document.getElementById('campus_select'); if(!sel) return; sel.addEventListener('change',function(){ var fd=new FormData(); fd.append('campus_id', this.value); fetch('assets/inc/set_campus.php',{method:'POST',body:fd}).then(r=>r.json()).then(j=>{ if(j.success) location.reload(); else alert('Could not set campus: '+j.msg); }).catch(()=>alert('Request failed')); });})();
                                                </script>
                                                <?php
                                            }
                                        }

                                        <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                             <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity" class="col-form-label"><h3>Date From</h3></label>
                                                    <input type="date" required="required" name="datefrom" class="form-control" id="inputEmail4" placeholder="DD/MM/YYYY">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label"><h3>Date To</h3></label>
                                                   <input type="date" required="required" name="dateto" class="form-control" id="inputEmail4" placeholder="DD/MM/YYYY">
                                                </div>
                                                
                                            </div>
                                            
                                            


                                            <button type="submit" name="genreport" class="ladda-button btn btn-primary" >Process  Report</button>

                                        </form>
<hr>

                               

                        <!-- end row -->
                                       
                                        

<div class="row">
                            <div class="col-12">
                                <div class="card-box">
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
                                                <th data-hide="phone" style="color:white;">Date</th>                                              
                                                <th data-hide="phone" style="color:white;">Drug Name</th>
                                                <th data-hide="phone" style="color:white;">Opening Stock</th>
                                                <th data-hide="phone" style="color:white;">Add Stock</th>
                                                <th data-hide="phone" style="color:white;">Stock Out</th>
                                                <th data-hide="phone" style="color:white;">Closing Stock</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */
                                                // Apply campus filter to store_stock listing when available
                                                if ($store_has_campus && $campus_id) {
                                                    $ret = "SELECT * FROM store_stock WHERE campus_id = ? ORDER BY name ASC";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param('i', $campus_id);
                                                    $stmt->execute();
                                                    $res = $stmt->get_result();
                                                } else {
                                                    $ret = "SELECT * FROM store_stock ORDER BY name ASC";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->execute();
                                                    $res = $stmt->get_result();
                                                }
                                                $cnt=1;
                                                while($row=$res->fetch_object())
                                                {
                                            ?>

                                                <tbody>
                                                <tr>
                                                    <td><?php echo $cnt?></td>
                                                    <td><?php echo $row->date;?></td>
                                                    <td><?php echo $row->name;?></td>
                                                    <td><?php echo $row->opening;?></td>
                                                    <td><?php echo $row->addstock;?></td>
                                                    <td><?php $add=$row->addstock; $opn=$row->opening; $clos=$row->closing; $tot=$add + $opn; echo $tot-$clos;?></td>
                                                    <td><?php echo $row->closing;?></td>

                                                    
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

                                <button type="submit" onClick="window.print()" class="ladda-button btn btn-primary" >Print Report</button>
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
        
    </body>

</html>