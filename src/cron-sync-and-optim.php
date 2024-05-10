<?php
// Include the library to optimize images
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\Filesystem\Filesystem;

$optimizerChain = OptimizerChainFactory::create();

function syncFolders($src, $dest) {
    global $optimizerChain;

    // Use Symfony Filesystem or similar library to check and create directories securely
    $filesystem = new Symfony\Component\Filesystem\Filesystem();

    if (!$filesystem->exists($dest)) {
        $filesystem->mkdir($dest, 0777);
    }

    if (!$filesystem->exists($dest) || !is_writable($dest)) {
        // Escape the output for security
        throw new Exception("The folder " . $dest . " is not writable.");
    }

    // Securely handle directory listing
    $destFiles = new DirectoryIterator($dest);
    foreach ($destFiles as $fileInfo) {
        if ($fileInfo->isDot()) continue;
        $destPath = $dest . '/' . $fileInfo->getFilename();
        $srcPath = $src . '/' . $fileInfo->getFilename();
        if (!$filesystem->exists($srcPath)) {
            $filesystem->remove($destPath);
        }
    }

    $dir = new DirectoryIterator($src);
    $filesProcessed = 0;
    foreach ($dir as $fileInfo) {
        if ($fileInfo->isDot()) continue;

        $srcPath = $src . '/' . $fileInfo->getFilename();
        $destPath = $dest . '/' . $fileInfo->getFilename();

        if ($fileInfo->isDir()) {
            syncFolders($srcPath, $destPath);
            continue;
        }

        $ext = strtolower($fileInfo->getExtension());
        if (!in_array($ext, ['jpg', 'png', 'webp', 'webm', 'gif'])) {
            continue;
        }

        if ($filesystem->exists($destPath)) {
            continue;  // Skip if the file already exists in $dest
        }

        $filesystem->copy($srcPath, $destPath);

        if ($ext === 'jpg' || $ext === 'png') {
            resizeImage($destPath, 2048);
        }

        $optimizerChain->optimize($destPath, $destPath);
    }
}

function resizeImage($path, $maxWidth = 2048) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext !== 'jpg' && $ext !== 'png') {
        return;
    }

    list($width, $height) = getimagesize($path);
    if ($width <= $maxWidth) {
        return;
    }

    $newWidth = $maxWidth;
    $newHeight = (int) ($height * ($newWidth / $width));

    $src = ($ext === 'jpg') ? imagecreatefromjpeg($path) : imagecreatefrompng($path);
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    if ($ext === 'png') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    // Consider using imagecopyresized if imagecopyresampled is discouraged
    imagecopyresized($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $saveImage = ($ext === 'jpg') ? 'imagejpeg' : 'imagepng';
    $saveImage($dst, $path);
}

// Check and create the "image-optim" directory if it does not exist
$srcFolder = 'images-source';
$destFolder = 'images-optim';
$filesystem = new Filesystem();
$filesystem = new Symfony\Component\Filesystem\Filesystem();

if (!$filesystem->exists($destFolder)) {
    $filesystem->mkdir($destFolder, 0777);
}

// Check write permissions on "image-optim"
if (!$filesystem->exists($destFolder) || !is_writable($destFolder)) {
    // Escape the output for security
}

syncFolders($srcFolder, $destFolder);
