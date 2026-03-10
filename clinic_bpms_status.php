<?php
session_start();
include('assets/inc/config.php');
include('assets/inc/checklogins.php');
check_login();
authorize();

// If a BPMS transaction ID is submitted, send the user to the detailed BPMS report page
if (isset($_POST['transid']) && trim($_POST['transid']) !== '') {
    $tid = trim($_POST['transid']);
    header('Location: bpms-report.php?request_id=' . urlencode($tid));
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
    <?php include('assets/inc/head.php');?>
<body>

    <div id="wrapper">
        <?php include('assets/inc/nav_r.php');?>
        <?php include('assets/inc/sidebar_cash.php');?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item active">BPMS Payment Status</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">BPMS Payment Status</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8 col-xl-6">
                            <div class="card-box">
                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-credit-card mr-1"></i> Check BPMS Transaction</h5>
                                <form method="post">
                                    <div class="form-group">
                                        <label for="transid">BPMS Transaction / Request ID</label>
                                        <input type="text" name="transid" id="transid" class="form-control" required placeholder="Enter BPMS transaction ID (request_id)">
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-success waves-effect waves-light mt-2">
                                            <i class="mdi mdi-magnify"></i> View BPMS Report
                                        </button>
                                    </div>
                                </form>
                                <p class="mt-3 text-muted">
                                    Use this page to look up the status of a payment that was redirected to BPMS. Enter the BPMS transaction ID shown on the invoice or payment gateway screen.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php include('assets/inc/footer.php');?>
        </div>
    </div>

    <div class="rightbar-overlay"></div>

    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>
</body>
</html>
