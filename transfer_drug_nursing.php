<?php
session_start();
include('assets/inc/config.php');

$q = $mysqli->query("SELECT id, name, quantity, supplier_name FROM drug ORDER BY name ASC");
$camp_q = $mysqli->query("SELECT id, name FROM campus_locations ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">

    <!-- Head -->
    <?php include('assets/inc/head.php'); ?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar -->
            <?php include('assets/inc/nav_r.php'); ?>

            <!-- Left Sidebar -->
            <?php include('assets/inc/sidebar_admin.php'); ?>

            <!-- Start Page Content -->
            <div class="content-page">
                <div class="content">
                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Transfer Drug to Nursing / Sickbay</h4>

                                        <?php if (!empty($_SESSION['err'])) { echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['err']).'</div>'; unset($_SESSION['err']); }
                                        if (!empty($_SESSION['success'])) { echo '<div class="alert alert-success">'.htmlspecialchars($_SESSION['success']).'</div>'; unset($_SESSION['success']); } ?>

                                        <form method="post" action="scripts/transfer_to_nursing.php" id="transferForm" novalidate>
                                            <div class="form-row">
                                                <div class="form-group col-md-8">
                                                    <label for="drug_id">Select Drug</label>
                                                    <select name="drug_id" id="drug_id" class="form-control" required>
                                                        <option value="">-- choose drug --</option>
                                                        <?php while ($d = $q->fetch_assoc()) {
                                                            $display = htmlspecialchars($d['name']);
                                                            echo '<option value="'.intval($d['id']).'" data-qty="'.intval($d['quantity']).'" data-supplier="'.htmlspecialchars($d['supplier_name']).'">'. $display . ' (' . intval($d['quantity']) . ')</option>'; }
                                                        ?>
                                                    </select>
                                                    <small class="form-text text-muted">Choose a drug to transfer to sickbay.</small>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="campus_id">Transfer To (Location)</label>
                                                    <select name="campus_id" id="campus_id" class="form-control">
                                                        <option value="">-- choose location --</option>
                                                        <?php while ($c = $camp_q->fetch_assoc()) {
                                                            echo '<option value="'.intval($c['id']).'">'.htmlspecialchars($c['name']).'</option>'; }
                                                        ?>
                                                    </select>
                                                    <small class="form-text text-muted">Or create a new location below.</small>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="quantity">Quantity to transfer</label>
                                                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="patient_code">Patient code (optional)</label>
                                                    <input type="text" name="patient_code" id="patient_code" class="form-control">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="notes">Notes (optional)</label>
                                                    <input type="text" name="notes" id="notes" class="form-control">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-8">
                                                    <label for="new_location">Create New Location (optional)</label>
                                                    <input type="text" name="new_location" id="new_location" class="form-control" placeholder="e.g. Drug to Nurse (Ibogun)">
                                                    <small class="form-text text-muted">If provided, a new location will be created and used for this transfer.</small>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="col">
                                                    <button type="submit" class="btn btn-primary">Transfer to Nursing</button>
                                                    <a href="setup_drugs.php" class="btn btn-outline-secondary ml-2">Back to Drugs</a>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div> <!-- content -->
            </div> <!-- content-page -->

        </div> <!-- wrapper -->

        <!-- Inline script for stock preview and validation -->
        <script>
        (function(){
            var drugSelect = document.getElementById('drug_id');
            var stockEl = document.getElementById('currentStock');
            var qtyInput = document.getElementById('quantity');
            var form = document.getElementById('transferForm');

            function updateStockDisplay(){
                var opt = drugSelect.options[drugSelect.selectedIndex];
                if (!opt || !opt.value) {
                    stockEl.textContent = '-';
                    return;
                }
                var qty = parseInt(opt.getAttribute('data-qty')||0, 10);
                var supplier = opt.getAttribute('data-supplier') || '';
                stockEl.textContent = qty + (supplier? (' — ' + supplier) : '');
            }

            drugSelect.addEventListener('change', function(){ updateStockDisplay(); });

            form.addEventListener('submit', function(e){
                var opt = drugSelect.options[drugSelect.selectedIndex];
                if (!opt || !opt.value) { e.preventDefault(); alert('Please select a drug.'); return false; }
                var avail = parseInt(opt.getAttribute('data-qty')||0, 10);
                var want = parseInt(qtyInput.value||0, 10);
                if (isNaN(want) || want <= 0) { e.preventDefault(); alert('Please enter a valid quantity to transfer.'); return false; }
                if (want > avail) { e.preventDefault(); alert('Transfer quantity exceeds current stock ('+avail+').'); return false; }
                return true;
            });

            // Initialize
            updateStockDisplay();
        })();
        </script>

    </body>

</html>
