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
            // Expecting: name, quantity, amount, category
            $name = strtoupper(trim($data[0]));
            $quantity = trim($data[1]);
            $amount = trim($data[2]);
            $category = trim($data[3]);

            // Only insert if name is not empty
            if (!empty($name)) {
                $stmt = $mysqli->prepare("INSERT INTO drug(name, quantity, amount, category) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssss", $name, $quantity, $amount, $category);
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
    fputcsv($output, ['name', 'quantity', 'amount', 'category']); // Header row
    fputcsv($output, ['Paracetamol', '100', '50', 'Tab']);        // Example row
    fclose($output);
    exit();
}
?>

<form method="POST" action="drug_csv_handler.php" enctype="multipart/form-data">
    <input type="file" name="drug_file" accept=".csv" required>
    <button type="submit" class="btn btn-success btn-sm">Upload</button>
</form>

<a href="drug_csv_handler.php?download_format=1" class="btn btn-outline-primary btn-sm">Download Format</a>