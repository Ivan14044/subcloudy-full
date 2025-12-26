<?php
/**
 * Скрипт массовой оптимизации изображений на сервере.
 * Ресайзит и сжимает изображения в указанных директориях.
 */

$public_root = '/var/www/subcloudy/public';
$storage_root = '/var/www/subcloudy/ai-bot-main/storage/app/public';

$configs = [
    // [path, max_width, max_height, quality]
    [$public_root . '/img/logo_trans.webp', 160, 160, 75],
    [$storage_root . '/logos', 120, 120, 75],
    [$storage_root . '/articles', 400, 225, 75],
    [$storage_root . '/savings-blocks', 128, 128, 75],
];

function optimize_image($file, $maxWidth, $maxHeight, $quality) {
    if (!file_exists($file)) return;
    
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($ext !== 'webp' && $ext !== 'png' && $ext !== 'jpg' && $ext !== 'jpeg') return;

    $info = getimagesize($file);
    if (!$info) return;
    
    list($width, $height, $type) = $info;

    // Считаем коэффициент масштабирования
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    if ($ratio >= 1) {
        // Если изображение уже меньше целевого размера, просто пересохраняем для сжатия
        $newWidth = $width;
        $newHeight = $height;
    } else {
        $newWidth = floor($width * $ratio);
        $newHeight = floor($height * $ratio);
    }

    $src = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($file); break;
        case IMAGETYPE_PNG:  $src = imagecreatefrompng($file); break;
        case IMAGETYPE_WEBP: $src = imagecreatefromwebp($file); break;
    }

    if (!$src) return;

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    
    // Сохраняем прозрачность
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $originalSize = filesize($file);
    
    // Всегда сохраняем как WebP для максимальной оптимизации
    $targetFile = $file;
    // Если нужно сохранить расширение, можно использовать imagewebp($dst, $file, $quality)
    // Но PageSpeed просит современные форматы.
    if (imagewebp($dst, $targetFile, $quality)) {
        $newSize = filesize($targetFile);
        echo "Optimized: " . basename($file) . " ({$width}x{$height} -> {$newWidth}x{$newHeight}) " . 
             round($originalSize/1024, 1) . "KB -> " . round($newSize/1024, 1) . "KB\n";
    }

    imagedestroy($src);
    imagedestroy($dst);
}

foreach ($configs as $config) {
    list($path, $maxWidth, $maxHeight, $quality) = $config;
    
    if (is_file($path)) {
        optimize_image($path, $maxWidth, $maxHeight, $quality);
    } elseif (is_dir($path)) {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            optimize_image($path . '/' . $file, $maxWidth, $maxHeight, $quality);
        }
    } else {
        echo "Path not found: $path\n";
    }
}

