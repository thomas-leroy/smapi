<?php
// Include the library to optimize images
// Install it via Composer: composer require spatie/image-optimizer
require 'vendor/autoload.php';
use Spatie\ImageOptimizer\OptimizerChainFactory;

$optimizerChain = OptimizerChainFactory::create();

function syncFolders($src, $dest) {
    global $optimizerChain;

    if (!is_dir($dest)) {
        mkdir($dest, 0777, true);
    }

    if (!is_writable($dest)) {
        die("The folder $dest is not writable.");
    }

    // Remove files from $dest that do not exist in $src
    $destFiles = array_diff(scandir($dest), ['.', '..']);
    foreach ($destFiles as $file) {
        $destPath = $dest . '/' . $file;
        $srcPath = $src . '/' . $file;
        if (!file_exists($srcPath)) {
            unlink($destPath);
        }
    }

    $dir = opendir($src);
    $filesProcessed = 0;
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $srcPath = $src . '/' . $file;
        $destPath = $dest . '/' . $file;

        if (is_dir($srcPath)) {
            syncFolders($srcPath, $destPath);
            continue;
        }

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (!in_array($ext, ['jpg', 'png', 'webp', 'webm', 'gif'])) {
            continue;
        }

        if (file_exists($destPath)) {
            continue;  // Skip if the file already exists in $dest
        }

        copy($srcPath, $destPath);

        if ($ext === 'jpg' || $ext === 'png') {
            resizeImage($destPath, 2048);
        }

        $optimizerChain->optimize($destPath, $destPath);
    }

    closedir($dir);
}

function resizeImage($path, $maxWidth = 2048) {
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if ($ext !== 'jpg' && $ext !== 'png') {
        return;
    }

    list($width, $height) = getimagesize($path);
    if ($width <= $maxWidth) {
        return;
    }

    $newWidth = $maxWidth;
    $newHeight = intval($height * ($newWidth / $width));

    $src = ($ext === 'jpg') ? imagecreatefromjpeg($path) : imagecreatefrompng($path);
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    if ($ext === 'png') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $saveImage = ($ext === 'jpg') ? 'imagejpeg' : 'imagepng';
    $saveImage($dst, $path);
}


// Check and create the "image-optim" directory if it does not exist
$srcFolder = 'images-source';
$destFolder = 'images-optim';

if (!is_dir($destFolder)) {
    mkdir($destFolder, 0777, true);
}

// Check write permissions on "image-optim"
if (!is_writable($destFolder)) {
    die("The folder $destFolder is not writable.");
}

syncFolders($srcFolder, $destFolder);
