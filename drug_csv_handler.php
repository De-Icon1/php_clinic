<?php
session_start();
include('assets/inc/config.php');
include('assets/inc/functions.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // === Handle Drug CSV Upload ===
    if (isset($_FILES['drug_file']['tmp_name'])) {
        $file = $_FILES['drug_file']['tmp_name'];
        $handle = fopen($file, "r");

        if (!$handle) {
            die("Error opening the file.");
        }

        $header = fgetcsv($handle); // Skip header row

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Expecting: name, quantity, amount, category, tabs_per_sachet, supplier_name, lpo_ref
                $name = strtoupper(trim(isset($data[0]) ? $data[0] : ''));
                $quantity = trim(isset($data[1]) ? $data[1] : '0');
                $amount = trim(isset($data[2]) ? $data[2] : '0');
                $category = trim(isset($data[3]) ? $data[3] : '');
                $tabs_per_sachet = isset($data[4]) ? (int) trim($data[4]) : 0;
                $supplier_name = isset($data[5]) ? trim($data[5]) : null;
                $lpo_ref = isset($data[6]) ? trim($data[6]) : null;

            // Only insert if name is not empty
            if (!empty($name)) {
                $stmt = $mysqli->prepare("INSERT INTO drug(name, quantity, amount, category, tabs_per_sachet, supplier_name, lpo_ref) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssssiss", $name, $quantity, $amount, $category, $tabs_per_sachet, $supplier_name, $lpo_ref);
                    $stmt->execute();
                }
            }
        }

        fclose($handle);
        // Redirect back with success message
        header("Location: setup_drug.php?msg=upload_success");
        exit();
    }
}

// === Handle Format CSV Download ===
if (isset($_GET['download_format'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="drug_format_template.csv"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['name', 'quantity', 'amount', 'category', 'tabs_per_sachet', 'supplier_name', 'lpo_ref']); // Header row
    fputcsv($output, ['Paracetamol', '100', '50', 'Tab', '10', 'Acme Pharma Ltd', 'LPO-12345']);        // Example row
    fclose($output);
    exit();
}
?>

<form method="POST" action="drug_csv_handler.php" enctype="multipart/form-data">
    <input type="file" name="drug_file" accept=".csv" required>
    <button type="submit" class="btn btn-success btn-sm">Upload</button>
</form>

<a href="drug_csv_handler.php?download_format=1" class="btn btn-outline-primary btn-sm">Download Format</a>