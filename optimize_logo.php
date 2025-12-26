<?php
$src = '/var/www/subcloudy/public/img/logo_trans.webp';
if (!file_exists($src)) {
    $src = '/var/www/subcloudy/ai-bot-main/public/img/logo_trans.webp';
}

if (file_exists($src)) {
    $img = imagecreatefromwebp($src);
    if ($img) {
        $resized = imagescale($img, 80, 80);
        if ($resized) {
            imagewebp($resized, $src, 80); // 80 quality
            echo "Successfully resized and compressed logo_trans.webp\n";
            imagedestroy($resized);
        }
        imagedestroy($img);
    } else {
        echo "Failed to load image from $src\n";
    }
} else {
    echo "Image not found at $src\n";
}
