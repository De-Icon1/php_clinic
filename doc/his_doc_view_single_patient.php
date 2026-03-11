<?php
    include('assets/inc/config.php');
    include('assets/inc/checklogin.php');
    check_login();

  $doc_id=$_SESSION['doc_id'];
  $working_location_id = isset($_SESSION['working_location_id']) && is_numeric($_SESSION['working_location_id']) ? (int)$_SESSION['working_location_id'] : null;
  $working_location_name = isset($_SESSION['working_location']) ? $_SESSION['working_location'] : '';
      $pat_id = isset($_GET['pat_id']) ? $_GET['pat_id'] : '';
      $pat_name = isset($_GET['pat_name']) ? $_GET['pat_name'] : '';
  //$doc_number = $_SERVER['doc_number'];
    $date=date('Y-m-d');

// Resolve staff working location / campus for scoping and labels
$campus_id = $working_location_id ?: (isset($_SESSION['campus_id']) && is_numeric($_SESSION['campus_id']) ? (int) $_SESSION['campus_id'] : null);

$location_label = '';
if (!empty($working_location_name)) {
    $location_label = $working_location_name;
} elseif ($campus_id) {
    $campusNameStmt = $mysqli->prepare("SELECT name FROM campus_locations WHERE id = ? LIMIT 1");
    if ($campusNameStmt) {
        $campusNameStmt->bind_param('i', $campus_id);
        $campusNameStmt->execute();
        $cRes = $campusNameStmt->get_result();
        if ($cRow = $cRes->fetch_assoc()) {
            $location_label = $cRow['name'];
        }
        $campusNameStmt->close();
    }

    if ($location_label === '' || $location_label === null) {
        $location_label = 'Location ID ' . $campus_id;
    }
}

            $result=mysqli_query($mysqli,"select * from doc_procedure where date='$date'");
            $reply = mysqli_fetch_array($result);
            $num = mysqli_num_rows($result);
            if($num >  0){
                $amnt=$reply['Total'];
                $bal=$amnt;
           }else{
                $bal='0';
            }


     $cons = 0;
$proc = getPro($mysqli);
$drg  = getPattotdrug($mysqli, $date, $pat_id); // returns sum(amount)
$tot  = $cons + $proc + $drg; // overall total bill           

if(isset($_GET['dels'])){
    $id=$_GET['dels'];
            $query="delete from drug_prescription where id=?";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('s',$id);
            $stmt->execute();
            if($stmt)
            {
                $success = "Drug Deleted Successfully";
                header("location: his_doc_view_single_patient.php?pat_id=$pat_id&&pat_name=$pat_name;");

            }
            else {
                $err = "Please Try Again Or Try Later";
            }
}

 if(isset($_POST['Refer'])){
  $rbox=trim($_POST['slbox']);
  $slab=trim($_POST['slab']);
  $name=$_GET['pat_name'];
  $date=date('Y-m-d');
  $result='';
  $category='';

        
    if(!empty($rbox) && empty($slab)){
            $sql="insert into refer values(0,'$date','$name','$rbox')";
            $stmt=mysqli_query($mysqli,$sql); 

            $query="insert into patient_scan(date,code,name,test,result,category) values(?,?,?,?,?,?)";
                    $st = $mysqli->prepare($query);
                    $rc=$st->bind_param('ssssss',$date,$pat_id,$name,$rbox,$result,$category);
                    $st->execute();

             if($st){
                        $success = "Patient Refered For Test Successfully";
                    }
                    else {
                        $err = "Please Try Again Or Try Later";
                    }
            }
            elseif(empty($rbox) && !empty($slab)){

            $query="insert into patient_lab(date,code,name,test,result,category) values(?,?,?,?,?,?)";
                    $st = $mysqli->prepare($query);
                    $rc=$st->bind_param('ssssss',$date,$pat_id,$name,$slab,$result,$category);
                    $st->execute();

             if($st){
                        $success = "Patient Refered For Test Successfully";
                    }
                    else {
                        $err = "Please Try Again Or Try Later";
                    }
            }

            elseif(!empty($rbox) && !empty($slab)){
                $sql="insert into refer values(0,'$date','$name','$rbox')";
                $stmt=mysqli_query($mysqli,$sql); 

            $query="insert into patient_scan(date,code,name,test,result,category) values(?,?,?,?,?,?)";
                    $st = $mysqli->prepare($query);
                    $rc=$st->bind_param('ssssss',$date,$pat_id,$name,$rbox,$result,$category);
                    $st->execute();

              $query2="insert into patient_lab(date,code,name,test,result,category) values(?,?,?,?,?,?)";
                    $st2 = $mysqli->prepare($query2);
                    $rc2=$st2->bind_param('ssssss',$date,$pat_id,$name,$slab,$result,$category);
                    $st2->execute();      

            if($stmt)
                    {
                        $success = "Patient Refered For Test Successfully";
                    }
                    else {
                        $err = "Please Try Again Or Try Later";
                    }
       } 

        else{$err = "No Action Selected Please Try Again Or Try Later";
        
        }


      }
if (isset($_POST['prdrug'])) {
    $date = date('Y-m-d');
    $drug = trim($_POST['drug']);
    $qnt = floatval(trim($_POST['qnt']));
    $const_raw = trim($_POST['const']);
    $dcate = trim($_POST['dcate']);
    $duration_raw = trim($_POST['duration']);

    // Normalize/parse duration and frequency
    $dur = getdurtn($duration_raw);
    $hly = gethly($const_raw);

    // Total dosage over full duration (ensure numeric math)
    $tot = floatval($qnt) * floatval($hly) * floatval($dur);

    // Build display string for table (no numeric-to-string concatenation before math)
    if (intval($tot) == $tot) {
        $tot_display = intval($tot) . $dcate;
    } else {
        $tot_display = rtrim(rtrim(number_format($tot, 4, '.', ''), '0'), '.') . $dcate;
    }

    // Ensure we have a numeric id to insert in case the table's `id` is not AUTO_INCREMENT
    $nid_res = $mysqli->query("SELECT IFNULL(MAX(id),0)+1 AS nid FROM drug_prescription");
    $new_id = 1;
    if ($nid_res) {
        $nid_row = $nid_res->fetch_assoc();
        $new_id = (int) ($nid_row['nid'] ?? 1);
    }

    // Insert using prepared statement with explicit field names (including id)
    $sql = "INSERT INTO drug_prescription 
(id, date, patid, name, drug, qnt, const, duration, total, totdrug, amount, cate)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }

    // Compute total amount (respect doctor's working location / campus if available)
    $dtot = getdrugtot($mysqli, $drug, $tot, $campus_id);
    if (!isset($dtot) || $dtot === null || $dtot === '' || !is_numeric($dtot)) {
        $dtot = 0;
    }

    $stmt->bind_param('isssssssssss',
        $new_id, $date, $pat_id, $pat_name, $drug, $qnt, $const_raw, $duration_raw, $tot, $tot_display, $dtot, $dcate
    );

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $success = "Drug processed successfully!";
}



 if(isset($_POST['finalsub'])){
    $date=date('Y-m-d');
    $visit   = isset($_POST['visit'])   ? trim($_POST['visit'])   : '';
    $diag    = isset($_POST['diagbox']) ? trim($_POST['diagbox']) : '';
    $pro     = isset($_POST['probox'])  ? trim($_POST['probox'])  : '';
    $plan    = isset($_POST['plan'])    ? trim($_POST['plan'])    : '';
    $slbox   = isset($_POST['slbox'])   ? trim($_POST['slbox'])   : '';
    $proamnt = isset($_POST['proamnt']) ? floatval($_POST['proamnt']) : 0;
    $cons    = isset($_POST['cons'])    ? floatval($_POST['cons'])    : 0;
    $totbill=$proamnt + $cons;

    // Compute explicit ids for visit record and patient bill in case their id columns lack AUTO_INCREMENT
    $ovr_id = 1;
    if ($resOv = $mysqli->query("SELECT IFNULL(MAX(id),0)+1 AS nid FROM outpatient_visist_record")) {
        if ($rowOv = $resOv->fetch_assoc()) {
            $ovr_id = (int)($rowOv['nid'] ?? 1);
        }
    }

    $pb_id = 1;
    if ($resPb = $mysqli->query("SELECT IFNULL(MAX(id),0)+1 AS nid FROM patient_bill")) {
        if ($rowPb = $resPb->fetch_assoc()) {
            $pb_id = (int)($rowPb['nid'] ?? 1);
        }
    }

    // Use prepared statements for inserts, accounting for possible legacy `amnt` columns
    // Check if outpatient_visist_record has an 'amnt' column
    $ovr_has_amnt = 0;
    if ($ovr_col_res = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='outpatient_visist_record' AND COLUMN_NAME='amnt'")) {
        if ($ovr_col_row = $ovr_col_res->fetch_assoc()) {
            $ovr_has_amnt = (int)($ovr_col_row['cnt'] ?? 0);
        }
    }

    if ($ovr_has_amnt) {
        // Newer/legacy schema with explicit amnt column
        $dstmt = $mysqli->prepare("INSERT INTO outpatient_visist_record (id, date, patid, name, diagnosis, proceedure, plan, doc_incharge, amnt) VALUES (?, ?, ?, ?, ?, ?, ?, '', ?)");
        if ($dstmt) {
            $dstmt->bind_param('isissssd', $ovr_id, $date, $pat_id, $pat_name, $diag, $pro, $plan, $totbill);
            $dstmt->execute();
            $dstmt->close();
        }
    } else {
        // Original schema without amnt column
        $dstmt = $mysqli->prepare("INSERT INTO outpatient_visist_record (id, date, patid, name, diagnosis, proceedure, plan, doc_incharge) VALUES (?, ?, ?, ?, ?, ?, ?, '')");
        if ($dstmt) {
            $dstmt->bind_param('isissss', $ovr_id, $date, $pat_id, $pat_name, $diag, $pro, $plan);
            $dstmt->execute();
            $dstmt->close();
        }
    }

    // Check if patient_bill has an 'amnt' column
    $pb_has_amnt = 0;
    if ($pb_col_res = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='patient_bill' AND COLUMN_NAME='amnt'")) {
        if ($pb_col_row = $pb_col_res->fetch_assoc()) {
            $pb_has_amnt = (int)($pb_col_row['cnt'] ?? 0);
        }
    }

    if ($pb_has_amnt) {
        // Schema with amnt column; populate it with the total bill
        $pstmt = $mysqli->prepare("INSERT INTO patient_bill (id, date, patid, name, const, proceedure, total, doc_incharge, amnt) VALUES (?, ?, ?, ?, ?, ?, ?, '', ?)");
        if ($pstmt) {
            // Types: id (i), date (s), patid (i), name (s), const (s), proceedure (s/num), total (s), amnt (d)
            $pstmt->bind_param('isissdsd', $pb_id, $date, $pat_id, $pat_name, $cons, $proamnt, $totbill, $totbill);
            $pstmt->execute();
            $pstmt->close();
        }
    } else {
        // Original schema without amnt column
        $pstmt = $mysqli->prepare("INSERT INTO patient_bill (id, date, patid, name, const, proceedure, total, doc_incharge) VALUES (?, ?, ?, ?, ?, ?, ?, '')");
        if ($pstmt) {
            // Keep types largely consistent with legacy usage, adding leading int for id
            $pstmt->bind_param('isissds', $pb_id, $date, $pat_id, $pat_name, $cons, $proamnt, $totbill);
            $pstmt->execute();
            $pstmt->close();
        }
    }

     $result=mysqli_query($mysqli,"select * from drug_prescription where date='$date' and patid='$pat_id'");
            while($reply = mysqli_fetch_array($result)){
                $drug=$reply['drug'];
                $qnt=$reply['qnt'];
                $const=$reply['const'];
                $duration=$reply['duration'];
                $total=$reply['total'];
                $totdrug=$reply['totdrug'];
                $amnt=$reply['amount'];
                 $cate=$reply['cate'];
                 // Insert into patient_drug_history using explicit id in case table lacks AUTO_INCREMENT
                 $ph_nid_res = $mysqli->query("SELECT IFNULL(MAX(id),0)+1 AS nid FROM patient_drug_history");
                 $ph_new_id = 1;
                 if ($ph_nid_res) {
                     $ph_row = $ph_nid_res->fetch_assoc();
                     $ph_new_id = (int) ($ph_row['nid'] ?? 1);
                 }
                 // Check if patient_drug_history has 'amount' and/or legacy 'amnt' columns in this DB
                 $pdh_has_amount = 0;
                 $pdh_has_amnt   = 0;

                 if ($pdh_col_res = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='patient_drug_history' AND COLUMN_NAME IN ('amount','amnt')")) {
                     while ($pdh_row = $pdh_col_res->fetch_assoc()) {
                         if ($pdh_row['COLUMN_NAME'] === 'amount') {
                             $pdh_has_amount = 1;
                         } elseif ($pdh_row['COLUMN_NAME'] === 'amnt') {
                             $pdh_has_amnt = 1;
                         }
                     }
                 }

                 if ($pdh_has_amount && $pdh_has_amnt) {
                     // Schema with both amount and amnt columns; populate both
                     $ph_stmt = $mysqli->prepare("INSERT INTO patient_drug_history (id, date, patid, name, drug, qnt, const, duration, total, totdrug, amount, amnt, cate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                     if ($ph_stmt) {
                         $ph_stmt->bind_param('issssssssssss', $ph_new_id, $date, $pat_id, $pat_name, $drug, $qnt, $const, $duration, $total, $totdrug, $amnt, $amnt, $cate);
                         $ph_stmt->execute();
                         $ph_stmt->close();
                     }
                 } elseif ($pdh_has_amount) {
                     // Schema with amount column only
                     $ph_stmt = $mysqli->prepare("INSERT INTO patient_drug_history (id, date, patid, name, drug, qnt, const, duration, total, totdrug, amount, cate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                     if ($ph_stmt) {
                         $ph_stmt->bind_param('isssssssssss', $ph_new_id, $date, $pat_id, $pat_name, $drug, $qnt, $const, $duration, $total, $totdrug, $amnt, $cate);
                         $ph_stmt->execute();
                         $ph_stmt->close();
                     }
                 } elseif ($pdh_has_amnt) {
                     // Schema with legacy amnt column only
                     $ph_stmt = $mysqli->prepare("INSERT INTO patient_drug_history (id, date, patid, name, drug, qnt, const, duration, total, totdrug, amnt, cate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                     if ($ph_stmt) {
                         $ph_stmt->bind_param('isssssssssss', $ph_new_id, $date, $pat_id, $pat_name, $drug, $qnt, $const, $duration, $total, $totdrug, $amnt, $cate);
                         $ph_stmt->execute();
                         $ph_stmt->close();
                     }
                 } else {
                     // Schema without amount/amnt columns
                     $ph_stmt = $mysqli->prepare("INSERT INTO patient_drug_history (id, date, patid, name, drug, qnt, const, duration, total, totdrug, cate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                     if ($ph_stmt) {
                         $ph_stmt->bind_param('issssssssss', $ph_new_id, $date, $pat_id, $pat_name, $drug, $qnt, $const, $duration, $total, $totdrug, $cate);
                         $ph_stmt->execute();
                         $ph_stmt->close();
                     }
                 }

                 // Insert pharmacy order and include campus/location when available
                   // Align with DB schema: pharmacy_order columns may use either `amount` or legacy `amnt`

                   // Compute a safe id in case pharmacy_order.id is not AUTO_INCREMENT
                   $po_nid_res = $mysqli->query("SELECT IFNULL(MAX(id),0)+1 AS nid FROM pharmacy_order");
                   $order_id = 1;
                   if ($po_nid_res) {
                       $po_row = $po_nid_res->fetch_assoc();
                       $order_id = (int) ($po_row['nid'] ?? 1);
                   }

                   // Base columns and values (amount/amnt columns appended conditionally below)
                   $order_cols = array('id', 'trackid', 'customer', 'drug', 'Qnt', 'const', 'status', 'date');
                   $order_values = array($order_id, $pat_id, $pat_name, $drug, $qnt, $const, 'Not Paid', $date);

                   // Check if pharmacy_order has amount / amnt and campus_id / pharmacy_location_id columns
                   $order_amount_col = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_order' AND COLUMN_NAME='amount'")->fetch_assoc()['cnt'] ?? 0;
                   $order_amnt_col   = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_order' AND COLUMN_NAME='amnt'")->fetch_assoc()['cnt'] ?? 0;
                   $order_campus_col = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_order' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
                   $order_loc_col    = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy_order' AND COLUMN_NAME='pharmacy_location_id'")->fetch_assoc()['cnt'] ?? 0;

                   // Prefer to populate whichever amount-style columns exist in this DB
                   if ($order_amount_col) {
                       $order_cols[] = 'amount';
                       $order_values[] = $amnt;
                   }
                   if ($order_amnt_col) {
                       $order_cols[] = 'amnt';
                       $order_values[] = $amnt;
                   }

                   if ($order_campus_col && $campus_id) {
                       $order_cols[] = 'campus_id';
                       $order_values[] = (int)$campus_id;
                   }

                   // Tie pharmacy order location to the staff working location when a pharmacy_location_id column exists
                   $location_id_for_order = $campus_id;
                   if ($order_loc_col && $location_id_for_order) {
                       $order_cols[] = 'pharmacy_location_id';
                       $order_values[] = (int)$location_id_for_order;
                   }

                   // Build prepared statement dynamically and bind robustly
                   $placeholders = implode(',', array_fill(0, count($order_cols), '?'));
                   $ins_sql = "INSERT INTO pharmacy_order (" . implode(',', $order_cols) . ") VALUES ($placeholders)";

                   $ins_stmt = $mysqli->prepare($ins_sql);
                   if ($ins_stmt) {
                       // build types string and references for bind_param
                       $types = '';
                       $refs = array();
                       foreach ($order_values as $idx => $val) {
                           // id, campus_id, pharmacy_location_id are ints; others can be bound as strings safely
                           if (is_int($val)) {
                               $types .= 'i';
                           } else {
                               $types .= 's';
                           }
                           $refs[$idx] = &$order_values[$idx];
                       }
                       array_unshift($refs, $types);
                       call_user_func_array(array($ins_stmt, 'bind_param'), $refs);
                       $ins_stmt->execute();
                   } else {
                       // Fallback: no prepared statement; attempt legacy-style insert including explicit id
                       $cols_str = '(' . implode(',', $order_cols) . ')';
                       $vals = array();
                       foreach ($order_values as $v) {
                           if (is_int($v)) {
                               $vals[] = (string)intval($v);
                           } else {
                               $vals[] = "'" . $mysqli->real_escape_string($v) . "'";
                           }
                       }
                       $sqll = "INSERT INTO pharmacy_order $cols_str VALUES (" . implode(',', $vals) . ")";
                       $sqs = mysqli_query($mysqli, $sqll);
                   }

                  $sdel="delete from drug_prescription where date='$date' and patid='$pat_id'";
                  $dq=mysqli_query($mysqli,$sdel);

                  $sdiag="delete from doc_diagnosis";
                  $sdiaq=mysqli_query($mysqli,$sdiag);

                  $spro="delete from doc_procedure";
                  $spq=mysqli_query($mysqli,$spro);  
            }
            //if($spq){
                $success="Doctor Has Succesfully Attended To You !!! ";
            //}else {
                //$err="Error In Posting ! Please Try Again";
           // }

}

function getdrugtot($mysqli, $dname, $qn, $location_id = null) {
    $bal = 0;
    $amnt = 0; // default value

    // Resolve effective location id: prefer explicit argument, then working_location_id, then legacy campus_id
    if (empty($location_id) || !is_numeric($location_id)) {
        if (isset($_SESSION['working_location_id']) && is_numeric($_SESSION['working_location_id'])) {
            $location_id = (int)$_SESSION['working_location_id'];
        } elseif (isset($_SESSION['campus_id']) && is_numeric($_SESSION['campus_id'])) {
            $location_id = (int)$_SESSION['campus_id'];
        } else {
            $location_id = null;
        }
    } else {
        $location_id = (int)$location_id;
    }

    // If pharmacy table exists with campus_id, prefer campus-specific amount
    $pharm_table_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy'")->fetch_assoc()['cnt'] ?? 0;
    $pharm_col_exists = 0;
    $pharm_loc_col_exists = 0;
    if ($pharm_table_exists) {
        $pharm_col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
        $pharm_loc_col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy' AND COLUMN_NAME='pharmacy_location_id'")->fetch_assoc()['cnt'] ?? 0;
    }

    // First preference: location-specific pricing (pharmacy_location_id or campus_id) tied to staff working location
    if ($pharm_table_exists && $pharm_loc_col_exists && $location_id) {
        $stmt = $mysqli->prepare("SELECT amount FROM pharmacy WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND pharmacy_location_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('si', $dname, $location_id);
            $stmt->execute();
            $stmt->bind_result($amnt);
            $stmt->fetch();
            $stmt->close();
        }
    }

    // Second preference: campus_id column (legacy multi-campus model) using same location id
    if (($amnt === 0 || $amnt === null || $amnt === '') && $pharm_table_exists && $pharm_col_exists && $location_id) {
        $stmt = $mysqli->prepare("SELECT amount FROM pharmacy WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND campus_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('si', $dname, $location_id);
            $stmt->execute();
            $stmt->bind_result($amnt);
            $stmt->fetch();
            $stmt->close();
        }
    }

    // Fallback within pharmacy table without any location filter
    if (($amnt === 0 || $amnt === null || $amnt === '') && $pharm_table_exists) {
        $stmt = $mysqli->prepare("SELECT amount FROM pharmacy WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $dname);
            $stmt->execute();
            $stmt->bind_result($amnt);
            $stmt->fetch();
            $stmt->close();
        }
    } else {
        // Fallback: attempt to find price in `drug` table if present
        $drug_table_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='drug'")->fetch_assoc()['cnt'] ?? 0;
        if ($drug_table_exists) {
            $stmt = $mysqli->prepare("SELECT amount FROM drug WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $dname);
                $stmt->execute();
                $stmt->bind_result($amnt);
                $stmt->fetch();
                $stmt->close();
            }
        }
    }

    // If no matching drug found or NULL amount, default to 0
    if ($amnt === null || $amnt === '') {
        $amnt = 0;
    }

    $bal = floatval($amnt) * floatval($qn);
    return $bal;
}

function getPattotdrug($mysqli,$date,$pid){
            $bal=0;
            $result=mysqli_query($mysqli,"select * from drug_prescription where date='$date' and patid='$pid'");
            while($reply = mysqli_fetch_array($result)){
                $amnt=$reply['amount'];
                $bal+=$amnt;
            }
            return $bal;
        }

 function getPro($mysqli){
        $date=date('Y-m-d');
            $bal=0;
            $result=mysqli_query($mysqli,"select * from doc_procedure where date='$date'");
            $reply = mysqli_fetch_array($result);
            $num = mysqli_num_rows($result);
             if($num >  0){
                $amnt=$reply['Total'];
                $bal=$amnt;

                return $bal;
           }else{
               return $bal=0;
            }

        }
function getdurtn($val){
    $v = trim($val);
    if ($v === '') return 0;

    // If duration is a fraction like "1/7", return the numerator (1)
    if (preg_match('/^(\d+)\s*\/\s*(\d+)$/', $v, $m)) {
        return intval($m[1]);
    }

    // If a plain number was provided (e.g., "7"), return that
    if (preg_match('/(\d+)/', $v, $m)) {
        return intval($m[1]);
    }

    return 0;
}

function gethly($val){
    $v = strtolower(trim($val));
    if ($v === '') return 0;

    // If a numeric hour is provided (e.g., "12" or "12hly"), compute doses per 24h
    if (preg_match('/(\d+(?:\.\d+)?)/', $v, $m)) {
        $hours = floatval($m[1]);
        if ($hours > 0) {
            // compute times per day; ensure at least 1
            $times = intval(round(24 / $hours));
            return max(1, $times);
        }
    }

    // Fallback mapping for legacy tokens
    $map = [
        '24hly' => 1,
        '12hly' => 2,
        '8hly'  => 3,
        '6hly'  => 4,
        '4hly'  => 6,
        '3hly'  => 8,
        '2hly'  => 10,
    ];
    if (isset($map[$v])) return $map[$v];

    return 0;
}
?>

<!DOCTYPE html>
    <html lang="en">

    <?php include('assets/inc/head.php');?>

    <body>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
             <?php include("assets/inc/nav.php");?>
            <!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
                <?php include("assets/inc/sidebar.php");?>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <!--Get Details Of A Single User And Display Them Here-->

            <?php
                 $pat_id = isset($_GET['pat_id']) ? $_GET['pat_id'] : '';
                 $pat_name = isset($_GET['pat_name']) ? $_GET['pat_name'] : '';
                // Apply campus scoping for sendsignal lookup
                $campus_id = isset($_SESSION['working_location_id']) && is_numeric($_SESSION['working_location_id']) ? (int)$_SESSION['working_location_id'] : (isset($_SESSION['campus_id']) && is_numeric($_SESSION['campus_id']) ? (int)$_SESSION['campus_id'] : null);
                $resCol = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id'");
                $hascamp = $resCol ? (int)$resCol->fetch_assoc()['cnt'] : 0;
                if ($hascamp) {
                    if ($campus_id) {
                        $rt = "SELECT * FROM sendsignal WHERE pat_code = ? AND campus_id = ? LIMIT 1";
                        $stt = $mysqli->prepare($rt);
                        $stt->bind_param('si', $pat_id, $campus_id);
                    } else {
                        // campus column exists but user has no campus assigned: do not return any record
                        $rt = "SELECT * FROM sendsignal WHERE 1 = 0";
                        $stt = $mysqli->prepare($rt);
                    }
                } else {
                    $rt = "SELECT * FROM sendsignal WHERE pat_code = ? LIMIT 1";
                    $stt = $mysqli->prepare($rt);
                    $stt->bind_param('s', $pat_id);
                }
                if ($stt) {
                    $stt->execute();
                    $rs = $stt->get_result();
                    $rw = $rs ? $rs->fetch_object() : null;
                    $time = $rw->Time ?? null;
                } else {
                    $rw = null; $time = null;
                }
                 
                /*$ret="SELECT  * FROM his_patients WHERE pat_id=?";
                $stmt= $mysqli->prepare($ret);
                $stmt->bind_param('i',$pat_id);
                $stmt->execute() ;//ok
                $res=$stmt->get_result();
                //$cnt=1;
                while($row=$res->fetch_object())
            {
                $mysqlDateTime = $row->pat_date_joined;*/

                $surn = $firstname = $mname = $phone = $cate = $nok = $add = $nokph = $dob = $date = $pic = "";

// Identify patient type
if (strpos($pat_id, 'IND') !== false) {
    $ret = "SELECT * FROM individual WHERE code = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'F') !== false) {
    $ret = "SELECT * FROM family_individual WHERE code = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'ST') !== false) {
    $ret = "SELECT * FROM student WHERE STcode = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'S') !== false) {
    $ret = "SELECT * FROM staff WHERE Scode = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'A') !== false) {   
    $ret = "SELECT * FROM antenatal WHERE Acode = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
elseif (strpos($pat_id, 'A') !== false) {
    $ret = "SELECT * FROM individual WHERE code = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $pat_id);
}
else {
    $stmt = null;
}

// Fetch if statement was prepared
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_object()) {
        $surn = $row->surname;
        $firstname = $row->firstname;
        $mname = $row->middlename;
        $phone = $row->phone;
        $add = $row->address ?? '';
        $nok = $row->nok ?? '';
        $nokph = $row->nok_phone ?? $row->nok_contact ?? '';
        $dob = $row->dob;
        $date = $row->reg_date;
        $pic = $row->picture;
        $cate = $cate ?: "PATIENT CARD";
    }
}
?>


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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Patients</a></li>
                                            <li class="breadcrumb-item active">View Patients</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title"><?php echo $surn." ".$firstname." ".$mname;?>'s Profile</h4>
                                    <?php if (!empty($location_label)): ?>
                                        <h5>Current Working Location: <?php echo htmlspecialchars($location_label); ?></h5>
                                    <?php endif; ?>
                               
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-4 col-xl-4">
                                <div class="card-box text-center">
                                    <img src="../picture/<?php echo $pic; ?>" width="170" height="150" 
                                        alt="profile-image">
                                    
                                    <div class="text-left mt-3">
                                        
                                        <p class="text-muted mb-2 font-13"><strong>Patient Number :</strong> <span class="ml-2"><?php echo $pat_id;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Full Name :</strong> <span class="ml-2"><?php echo $surn." ".$firstname." ".$mname;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Mobile :</strong><span class="ml-2"><?php echo $phone;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Card Type :</strong><span class="ml-2"><?php echo $cate;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Address :</strong> <span class="ml-2"><?php echo $add;?></span></p>
                                        <p class="text-muted mb-2 font-13"><strong>Date Of Birth :</strong> <span class="ml-2"><?php echo $dob;?></span></p>
                                        <div>
                                           <?php 
                                                if (!empty($dob)) {
                                                    try {
                                                        $dobDate = new DateTime($dob);
                                                        $today = new DateTime(); 
                                                        $age = $today->diff($dobDate)->y; 
                                                        echo '<p class="text-muted mb-2 font-13"><strong>Age :</strong> <span class="ml-2">' . $age . ' years</span></p>';
                                                    } catch (Exception $e) {
                                                        echo '<p class="text-danger">Invalid Date Format</p>';
                                                    }
                                                } else {
                                                    echo '<p class="text-muted mb-2 font-13"><strong>Age :</strong> <span class="ml-2">N/A</span></p>';
                                                }
                                            ?>
                                        </div>
                                        
                                        <p class="text-muted mb-2 font-13"><strong>Date Registered :</strong> <span class="ml-2"><?php echo date("d/m/Y - h:m", strtotime($date));?></span></p>
                                        <p class="text-muted mb-2 font-13"> <span class="ml-2"><a href="<?php  ?>"><strong><h3>Patient Medical History</h3></strong></a></span></p>
                                        <hr>




                                    </div>

                                </div> <!-- end card-box -->

                            </div> <!-- end col-->
                            
                           
                            <div class="col-lg-8 col-xl-8">
                                <div class="card-box">
                                    <ul class="nav nav-pills navtab-bg nav-justified">
                                         <li class="nav-item">
                                            <a href="#aboutdc" data-toggle="tab" aria-expanded="false" class="nav-link">
                                                Doctor's Portal
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#aboutme" data-toggle="tab" aria-expanded="true" class="nav-link">
                                               Scan/X-Ray Result
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#timeline" data-toggle="tab" aria-expanded="true" class="nav-link active">
                                                 Vitals
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#settings" data-toggle="tab" aria-expanded="true" class="nav-link">
                                                Laboratory
                                            </a>
                                        </li>

                                    </ul>
                                    <!--Medical History-->

                                    <form method="post" enctype="multipart/form-data">
                                    <div class="tab-content">
                                        <div class="tab-pane show " id="aboutdc">
                                            <div class="col-md-12">
                                                             <div class="form-group">
                                                              <label for="comment">Plan</label>
                                                              <textarea class="form-control" id="comment" name="plan" rows="3">
                                                              </textarea>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                             <div class="form-group">
                                                              <label for="comment">Observation</label>
                                                              <textarea class="form-control" id="comment" name="observation" rows="3">
                                                              </textarea>
                                                            </div>
                                                        </div>
                                            <div class="table-responsive">
                                                <table class="table table-borderless mb-0">
                                                
                                                       <div class="col-md-12">
                                                             <div class="form-group">
                                                              <label for="comment">Diagnosis</label>
                                                              <textarea class="form-control" id="comment" name="diagnosis" rows="3">
                                                              </textarea>
                                                            </div>
                                                            </div>
                                                        <div class="col-md-12">
                                                             <div class="form-group">
                                                              <label for="comment">Procedures</label>
                                                              <textarea class="form-control" id="comment" name="procedures" rows="3">
                                                                 </textarea>
                                                            </div>
                                                        </div> <!-- end col -->


                                                    </div> <!-- end row -->
                                                

                                                    <div class="row">
                                        
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="firstname">Refer For Scan Test</label>
                                                                   <select id="inputState" onChange="getslsubmit(this.value);" required="required" name="scan" class="form-control">
                                                            <option>Choose</option>
                                                        <?php
                                                            $sql = "SELECT * FROM scan order by id ASC";
                                                            $result = mysqli_query($mysqli,$sql);
                                                            while($reply = mysqli_fetch_array($result)){
                                                                echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";
                                                            }
                                                        ?>
                                                     
                                                    </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="lastname">Refer for Laboratory Test</label>
                                                               <select id="l" onChange="getslab(this.value);" required="required" name="lab"  class="form-control">
                                                        <option>Choose</option>
                                                         
                                                        <?php
                                                            $sql = "SELECT * FROM lab order by id ASC";
                                                            $result = mysqli_query($mysqli,$sql);
                                                            while($reply = mysqli_fetch_array($result)){
                                                                echo "<option value=\"".$reply['name']."\">".$reply['name']."</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                            </div>
                                                        </div> <!-- end col -->
                                                    </div>
                                                <div class="row">
                                               
                                                    <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="firstname">Scan Test</label>
                                                                <textarea class="form-control" id="slname" name="slbox" rows="2">
                                                              </textarea>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="firstname">Laboratory Test</label>
                                                                <textarea class="form-control" id="slab" name="slab" rows="2">
                                                              </textarea>
                                                            </div>
                                                        </div>

                                                         <div class="col-md-3">
                                                             <div class="text-left">
                                                            <button type="submit" name="Refer" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i>Refer Patient for Test</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                             <div class="text-left">
                                                            <button type="button"  class="btn btn-danger waves-effect waves-light mt-2" onclick="getdelete();" ><i class="mdi mdi-content-save"></i>Clear Diagnosis</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                             <div class="text-left">
                                                            <button type="button"  class="btn btn-danger waves-effect waves-light mt-2" onclick="getsdelete();" ><i class="mdi mdi-content-save"></i>Clear Scan & Laboratory</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                             <div class="text-left">
                                                            <button type="submit" name="prdrug" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i>Process Patient Drug</button>
                                                            </div>
                                                        </div>

                                                    </div> <!-- end row -->

                                                    <div class="row">

                                                    <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="lastname">List of Drugs</label>
                                                                <?php if (!empty($location_label)): ?>
                                                                    <small class="form-text text-muted">Listing drugs from <?php echo htmlspecialchars($location_label); ?> pharmacy</small>
                                                                <?php endif; ?>
                                                               <select id="name" name="drug"  class="form-control">
                                                            <option>Choose</option>
                                                         
                                                                <?php
                                                                    /* Prefer listing drugs available in the doctor's campus when the `pharmacy` table exists.
                                                                       Fallback to `pharmacy` without campus filter or `drug` table if pharmacy is not available. */
                                                                    $pharm_table_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy'")->fetch_assoc()['cnt'] ?? 0;
                                                                    $pharm_col_exists = 0;
                                                                    $pharm_loc_col_exists = 0;
                                                                    if ($pharm_table_exists) {
                                                                        $pharm_col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy' AND COLUMN_NAME='campus_id'")->fetch_assoc()['cnt'] ?? 0;
                                                                        $pharm_loc_col_exists = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pharmacy' AND COLUMN_NAME='pharmacy_location_id'")->fetch_assoc()['cnt'] ?? 0;
                                                                    }

                                                                    $location_id_for_drugs = $campus_id;

                                                                    if ($pharm_table_exists && $pharm_loc_col_exists && $location_id_for_drugs) {
                                                                        $ps = $mysqli->prepare("SELECT DISTINCT name FROM pharmacy WHERE pharmacy_location_id = ? ORDER BY name ASC");
                                                                        $ps->bind_param('i', $location_id_for_drugs);
                                                                        $ps->execute();
                                                                        $pres = $ps->get_result();
                                                                        while ($reply = $pres->fetch_assoc()) {
                                                                            echo "<option value=\"".htmlspecialchars($reply['name'])."\">".htmlspecialchars($reply['name'])."</option>";
                                                                        }
                                                                    } elseif ($pharm_table_exists && $pharm_col_exists && $location_id_for_drugs) {
                                                                        $ps = $mysqli->prepare("SELECT DISTINCT name FROM pharmacy WHERE campus_id = ? ORDER BY name ASC");
                                                                        $ps->bind_param('i', $location_id_for_drugs);
                                                                        $ps->execute();
                                                                        $pres = $ps->get_result();
                                                                        while ($reply = $pres->fetch_assoc()) {
                                                                            echo "<option value=\"".htmlspecialchars($reply['name'])."\">".htmlspecialchars($reply['name'])."</option>";
                                                                        }
                                                                    } elseif ($pharm_table_exists) {
                                                                        $ps = $mysqli->prepare("SELECT DISTINCT name FROM pharmacy ORDER BY name ASC");
                                                                        $ps->execute();
                                                                        $pres = $ps->get_result();
                                                                        while ($reply = $pres->fetch_assoc()) {
                                                                            echo "<option value=\"".htmlspecialchars($reply['name'])."\">".htmlspecialchars($reply['name'])."</option>";
                                                                        }
                                                                    } else {
                                                                        $sql = "SELECT * FROM drug order by id ASC";
                                                                        $result = mysqli_query($mysqli,$sql);
                                                                        while($reply = mysqli_fetch_array($result)){
                                                                            echo "<option value=\"".htmlspecialchars($reply['name'])."\">".htmlspecialchars($reply['name'])."</option>";
                                                                        }
                                                                    }
                                                                ?>
                                                                </select>
                                                                                                                        </div>
                                                                                                                </div> <!-- end col -->

                                                                                                            <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="dosage">Dosage</label>
                                                                <input type="text" name="qnt"  class="form-control" id="firstname" placeholder="Qty">
                                                            </div>
                                                    </div> 

                                                      <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="firstname">Const</label>
                                                                <input type="text" name="const"  class="form-control" id="firstname" placeholder="Hly">
                                                            </div>
                                                    </div> 
                                                    <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="firstname">Category</label>
                                                                   <select id="inputState" required="required" name="dcate"  class="form-control">
                                                             <option>Choose</option>
                                                             <option value="Tab">Tab</option>
                                                            <option value="Cream">Cream</option>
                                                            <option value="Drop">Drop</option>
                                                            <option value="Injection">Injection</option>
                                                     
                                                                </select>
                                                            </div>
                                                        </div> 

                                                    <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="firstname">Duration</label>
                                                                <input type="text" name="duration"  class="form-control" id="firstname" placeholder="Duration">
                                                            </div>
                                                    </div> 
                                                    </form>
                                            <div class="table-responsive">
                                        <table id="demo-foo-filtering" style="background-color:grey;" class="datatable-1 table table-bordered table-striped   display" data-page-size="7">
                                                <thead>
                                            <tr>
                                                <th data-hide="phone" style="color:white;">Date</th>
                                                <th data-hide="phone" style="color:white;">Drug</th>
                                                <th data-hide="phone" style="color:white;">Dosage</th>
                                                <th data-hide="phone" style="color:white;">Const</th>
                                                <th data-hide="phone" style="color:white;">Duration</th>
                                                 <th data-hide="phone" style="color:white;">Total</th>
                                                <th data-hide="phone" style="color:white;">Action</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            /*
                                                *get details of allpatients
                                                *
                                            */  
                                                $date=date('Y-m-d');
                                                $ret="SELECT * FROM drug_prescription where date='$date' ORDER BY id ASC "; 
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
                                                    <td><?php echo $row->const;?></td>
                                                     <td><?php echo $row->duration;?></td>
                                                    <td><?php echo $row->totdrug; ?></td>
                                                    
                                                    <td><a href="his_doc_view_single_patient.php?dels=<?php echo $row->id;?>"><img src="assets/img/remove.png" height="20" width="20"></a></td>
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

                                                    </div>

    
                                                </table>

                                                 <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Consultation</label>
                                                                <input type="text" value="0" style="background-color:darkgrey; color:red;" name="cons" class="form-control" id="firstname" placeholder="">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Drug Bill</label>
                                                                <input type="text" value="<?php echo getPattotdrug($mysqli,date('Y-m-d'),$pat_id); ?>" style="background-color:darkgrey; color:red; font-size:+45;" name="drugamnt" class="form-control" id="firstname" placeholder="">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Procedure</label>
                                                                <input type="text" value="<?php echo getPro($mysqli); ?>" style="background-color:darkgrey; color:red; font-size:+45;" name="proamnt" class="form-control" id="pro" placeholder="">
                                                            </div>
                                                        </div>

                                                         <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="firstname">Total Bill</label>
                                                                <input type="text" style="background-color:darkgrey; color:red;" name="tbill" value="<?php echo $cons + $proc + getPattotdrug($mysqli, date('Y-m-d'), $pat_id);?>" class="form-control" id="firstname" placeholder="">
                                                            </div>
                                                        </div>

                                                         <div class="col-md-5">
                                                             <div class="text-left">
                                                            <button type="submit" name="finalsub" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i>Save Record</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                            </div>
                                        </div>
                                        </form>
                                        <!-- end vitals content-->



                                        <div class="tab-pane show active" id="aboutme">
                                             <ul class="list-unstyled timeline-sm">
                                                <?php
                                                    $pres_pat_number =$pat_id;
                                                    $ret="SELECT  * FROM patient_scan where code='$pres_pat_number' and result !='' order by id DESC";
                                                    $stmt= $mysqli->prepare($ret) ;
                                                    // $stmt->bind_param('i',$pres_pat_number );
                                                    $stmt->execute() ;//ok
                                                    $res=$stmt->get_result();
                                                    //$cnt=1;
                                                    
                                                    while($row=$res->fetch_object())
                                                        {
                                                    $mysqlDateTime = $row->date; //trim timestamp to date

                                                ?>
                                                    <li class="timeline-sm-item">
                                                        <span class="timeline-sm-date"><?php echo date("Y-m-d", strtotime($mysqlDateTime));?></span>
                                                        <h5 class="mt-0 mb-1"><?php echo $row->test;?></h5>
                                                        <p class="text-muted mt-2">
                                                            <?php echo $row->result;?>
                                                        </p>

                                                    </li>
                                                <?php }?>
                                            </ul>
                                           
                                        </div> <!-- end tab-pane -->
                                        <!-- end Prescription section content -->

                                        <div class="tab-pane show " id="timeline">
                                            <div class="table-responsive">
                                                <table class="table table-borderless mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Body Temperature</th>
                                                            <th>Heart Rate/Pulse</th>
                                                            <th>Respiratory Rate</th>
                                                            <th>Blood Pressure</th>
                                                            <th>Date Recorded</th>
                                                        </tr>
                                                    </thead>
                                                    <?php
                                                        $vit_pat_number =$pat_id;
                                                        $ret="SELECT  * FROM his_vitals WHERE vit_pat_number = '$vit_pat_number' order by id DESC";
                                                        $stmt= $mysqli->prepare($ret) ;
                                                        // $stmt->bind_param('i',$vit_pat_number );
                                                        $stmt->execute() ;//ok
                                                        $res=$stmt->get_result();
                                                        //$cnt=1;
                                                        
                                                        while($row=$res->fetch_object())
                                                            {
                                                        $mysqlDateTime = $row->vit_daterec; //trim timestamp to date

                                                    ?>
                                                        <tbody>
                                                            <tr>
                                                                <td><?php echo $row->vit_bodytemp;?></td>
                                                                <td><?php echo $row->vit_heartpulse;?></td>
                                                                <td><?php echo $row->vit_resprate;?></td>
                                                                <td><?php echo $row->vit_bloodpress;?></td>
                                                                <td><?php echo date("Y-m-d", strtotime($mysqlDateTime));?></td>
                                                            </tr>
                                                        </tbody>
                                                    <?php }?>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- end vitals content-->

                                        <div class="tab-pane" id="settings">
                                            <ul class="list-unstyled timeline-sm">
                                                <?php
                                                    $pres_pat_number =$pat_id;
                                                    $ret="SELECT  * FROM patient_lab where code='$pres_pat_number' and result !='' order by id DESC";
                                                    $stmt= $mysqli->prepare($ret) ;
                                                    // $stmt->bind_param('i',$pres_pat_number );
                                                    $stmt->execute() ;//ok
                                                    $res=$stmt->get_result();
                                                    //$cnt=1;
                                                    
                                                    while($row=$res->fetch_object())
                                                        {
                                                    $mysqlDateTime = $row->date; //trim timestamp to date

                                                ?>
                                                    <li class="timeline-sm-item">
                                                        <span class="timeline-sm-date"><?php echo date("Y-m-d", strtotime($mysqlDateTime));?></span>
                                                        <h5 class="mt-0 mb-1"><?php echo $row->test;?></h5>
                                                        <p class="text-muted mt-2">
                                                            <?php echo $row->result;?>
                                                        </p>

                                                    </li>
                                                <?php }?>

                                            </ul>
                                        </div>
                                        </div>
                                        <!-- end lab records content-->

                                    </div> <!-- end tab-content -->
                                </div> <!-- end card-box-->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

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

        <!-- App js -->
        <script src="assets/js/app.min.js"></script>

    </body>

<script type="text/javascript">

        function getsubmit(val) {
    $.ajax({
    type: "POST",
    url: "get_dsubmit.php",
    data:'name='+val,
    success: function(data){
        $("#name").html(data);
    }
    });
}


function getpsubmit(val) {
    $.ajax({
    type: "POST",
    url: "get_psubmit.php",
    data:'name='+val,
    success: function(data){
        $("#pname").html(data);
    }
    });
    getproceed(val)
}
function getproceed(val) {
    $.ajax({
    type: "POST",
    url: "get_proceed.php",
    data:'name='+val,
    success: function(data){
        $("#pro").html(data);
    }
    });
}

function getslsubmit(val) {
    $.ajax({
    type: "POST",
    url: "get_slsubmit.php",
    data:'name='+val,
    success: function(data){
        $("#slname").html(data);
    }
    });
}

function getslab(val) {
    $.ajax({
    type: "POST",
    url: "get_labsubmit.php",
    data:'name='+val,
    success: function(data){
        $("#slab").html(data);
    }
    });
}

function getdelete(val) {
    $.ajax({
    type: "POST",
    url: "get_delete.php",
    data:'name='+val,
    success: function(data){
        $("#name").html(data);
        $("#pname").html(data);
    }
    });
}

function getsdelete(val) {
    $.ajax({
    type: "POST",
    url: "get_sdelete.php",
    data:'name='+val,
    success: function(data){
        $("#slname").html(data);
         $("#slab").html(data);
    }
    });
}

    </script>
</html>