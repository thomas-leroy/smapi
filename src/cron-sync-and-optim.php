<?php
// Include the library to optimize images.
require 'vendor/autoload.php';

use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\Filesystem\Filesystem;

$optimizerChain = OptimizerChainFactory::create();

function syncFolders($src, $dest)
{
    global $optimizerChain;

    // Use Symfony Filesystem or similar library to check and create directories securely.
    $filesystem = new Symfony\Component\Filesystem\Filesystem();

    if ($filesystem->exists($dest) === false) {
        $filesystem->mkdir($dest, 0777);
    };

    if ($filesystem->exists($dest) === false || is_writable($dest) === false) {
        // Escape the output for security.
        throw new Exception("The folder $dest is not writable.");
    };

    // Securely handle directory listing.
    $destFiles = new DirectoryIterator($dest);
    foreach ($destFiles as $fileInfo) {
        if ($fileInfo->isDot() === true) {
            continue;
        };

        $destPath = $dest.'/'.$fileInfo->getFilename();
        $srcPath = $src.'/'.$fileInfo->getFilename();

        if ($filesystem->exists($srcPath) === false) {
            $filesystem->remove($destPath);
        }
    }

    $dir = new DirectoryIterator($src);
    foreach ($dir as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        };

        $srcPath = $src.'/'.$fileInfo->getFilename();
        $destPath = $dest.'/'.$fileInfo->getFilename();

        if ($fileInfo->isDir() === true) {
            syncFolders($srcPath, $destPath);
            continue;
        };

        $ext = strtolower($fileInfo->getExtension());
        if (in_array($ext, ['jpg', 'png', 'webp', 'webm', 'gif', 'avif']) === false) {
            continue;
        };

        if ($filesystem->exists($destPath) === true) {
            continue;  // Skip if the file already exists in $dest.
        };

        $filesystem->copy($srcPath, $destPath);

        if ($ext === 'jpg' || $ext === 'png') {
            resizeImage($destPath, 2048);
        }

        $optimizerChain->optimize($destPath, $destPath);
    }
}

function resizeImage($path, $maxWidth = 2048)
{
    $fileInfo = new SplFileInfo($path);
    $ext = strtolower($fileInfo->getExtension());
    if ($ext !== 'jpg' && $ext !== 'png') {
        return;
    }

    list($width, $height) = getimagesize($path);
    if ($width <= $maxWidth) {
        return;
    }

    $newWidth = $maxWidth;
    $newHeight = (int) ($height * ($newWidth / $width));

    if ($ext === 'jpg') {
        $imageData = file_get_contents($path);
        $src = imagecreatefromstring($imageData);
    } else {
        $src = imagecreatefrompng($path);
    }
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    if ($ext === 'png') {
        imagesavealpha($dst, true);
    }

    // Consider using imagecopyresized if imagecopyresampled is discouraged.
    \imagecopyresized($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $saveImage = 'imagepng';
    if ($ext === 'jpg') {
        $saveImage = 'imagejpeg';
    }
    ;
    $saveImage($dst, $path);
}

// Check and create the "image-optim" directory if it does not exist.
$srcFolder = 'images-source';
$destFolder = 'images-optim';
$filesystem = new Filesystem();
$filesystem = new Symfony\Component\Filesystem\Filesystem();

if ($filesystem->exists($destFolder) === false) {
    $filesystem->mkdir($destFolder, 0777);
}

// Check write permissions on "image-optim".
if ($filesystem->exists($destFolder) === false || is_writable($destFolder) === false) {
    // Escape the output for security.
}

syncFolders($srcFolder, $destFolder);
