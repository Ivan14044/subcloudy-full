<?php
$file = '/var/www/subcloudy/public/img/logo_trans.webp';
if (file_exists($file)) {
    $i = imagecreatefromwebp($file);
    if ($i) {
        $w = imagesx($i);
        $h = imagesy($i);
        $new = imagecreatetruecolor(80, 80);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopyresampled($new, $i, 0, 0, 0, 0, 80, 80, $w, $h);
        imagewebp($new, $file, 30); // Очень агрессивное сжатие
        imagedestroy($i);
        imagedestroy($new);
        echo "Resized to 80x80 and optimized logo to 30% quality\n";
        echo "New size: " . filesize($file) . " bytes\n";
    }
}

