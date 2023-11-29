<?php
// Inclure la bibliothèque pour optimiser les images
// Installez-le via Composer : composer require spatie/image-optimizer
require 'vendor/autoload.php';
use Spatie\ImageOptimizer\OptimizerChainFactory;

$optimizerChain = OptimizerChainFactory::create();

function syncFolders($src, $dest) {
    global $optimizerChain;

    if (!is_dir($dest)) {
        mkdir($dest, 0777, true);
    }

    if (!is_writable($dest)) {
        die("Le dossier $dest n'est pas accessible en écriture.");
    }

    // Supprimer les fichiers de $dest qui n'existent pas dans $src
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
            continue;  // Skip si le fichier existe déjà dans $dest
        }

        copy($srcPath, $destPath);

        if ($ext === 'jpg') {
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


// Vérifier et créer le répertoire "image-optim" s'il n'existe pas
$srcFolder = 'images-source';
$destFolder = 'images-optim';

if (!is_dir($destFolder)) {
    mkdir($destFolder, 0777, true);
}

// Vérifier les droits d'écriture sur "image-optim"
if (!is_writable($destFolder)) {
    die("Le dossier $destFolder n'est pas accessible en écriture.");
}

syncFolders($srcFolder, $destFolder);
?>
