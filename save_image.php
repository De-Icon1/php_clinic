<?php
$data = json_decode(file_get_contents("php://input"));

if (isset($data->image)) {
    $imageData = $data->image;

    // Remove the "data:image/png;base64," part
    $base64 = explode(',', $imageData)[1];
    $image = base64_decode($base64);

    // Create a unique filename
    $filename = 'uploads/webcam_' . time() . '.png';

    // Ensure the uploads folder exists
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    // Save to file
    if (file_put_contents($filename, $image)) {
       // echo "Image saved as $filename";
    } else {
     //   echo "Failed to save image.";
    }
} else {
    echo "No image data received.";
}
?>