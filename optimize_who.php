<?php
$file = '/var/www/subcloudy/public/img/who.webp';
if (!file_exists($file)) {
    echo 'File not found: ' . $file . PHP_EOL;
    exit(1);
}

$src = imagecreatefromwebp($file);
if (!$src) {
    echo 'Failed to load image: ' . $file . PHP_EOL;
    exit(1);
}

$width = imagesx($src);
$height = imagesy($src);

// Resize to 800px width if it's larger
$targetWidth = 800;
if ($width > $targetWidth) {
    $targetHeight = floor($height * ($targetWidth / $width));
    $dst = imagecreatetruecolor($targetWidth, $targetHeight);
    
    // Preserve transparency for WebP if any
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
    echo "Resized from {$width}x{$height} to {$targetWidth}x{$targetHeight}" . PHP_EOL;
} else {
    $dst = $src;
    echo "No resize needed. Original size: {$width}x{$height}" . PHP_EOL;
}

$originalSize = filesize($file);
if (imagewebp($dst, $file, 70)) {
    $newSize = filesize($file);
    echo 'Optimization complete.' . PHP_EOL;
    echo 'Original size: ' . round($originalSize / 1024, 2) . ' KB' . PHP_EOL;
    echo 'New size: ' . round($newSize / 1024, 2) . ' KB' . PHP_EOL;
    echo 'Saved: ' . round(($originalSize - $newSize) / 1024, 2) . ' KB (' . round((1 - $newSize/$originalSize)*100, 1) . '%)' . PHP_EOL;
} else {
    echo 'Failed to save optimized image.' . PHP_EOL;
}

imagedestroy($src);
if ($dst !== $src) imagedestroy($dst);
