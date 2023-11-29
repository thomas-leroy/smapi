<?php
// Get the filename to be deleted from the request
$filename = $_POST['filename'];

// Define the folder path where the images are stored
$imageFolder = 'path/to/image/folder/';

// Construct the full path of the image file
$imagePath = $imageFolder . $filename;

// Check if the file exists before attempting to delete it
if (file_exists($imagePath)) {
    // Attempt to delete the file
    if (unlink($imagePath)) {
        // File has been successfully deleted
        echo 'Image deleted from server';
    } else {
        // Failed to delete the file
        http_response_code(500);
        echo 'Failed to delete the image';
    }
} else {
    // File does not exist
    http_response_code(404);
    echo 'Image not found';
}
?>