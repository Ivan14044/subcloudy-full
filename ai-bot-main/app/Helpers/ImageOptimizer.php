<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ImageOptimizer
{
    /**
     * Конвертирует изображение в WebP и удаляет оригинал
     * Автоматически конвертирует PNG, JPEG, GIF в WebP формат
     *
     * @param string $filePath Путь к файлу относительно storage/app/public
     * @param int $maxWidth Максимальная ширина
     * @param int $maxHeight Максимальная высота
     * @param int $quality Качество WebP (0-100)
     * @return string|null Путь к WebP файлу или null в случае ошибки
     */
    public static function convertToWebP(
        string $filePath,
        int $maxWidth = 280,
        int $maxHeight = 280,
        int $quality = 85
    ): ?string {
        // Проверяем, существует ли файл
        if (!Storage::disk('public')->exists($filePath)) {
            \Log::warning("Image file not found: {$filePath}");
            return null;
        }

        // Если файл уже WebP, возвращаем его путь
        if (preg_match('/\.webp$/i', $filePath)) {
            return $filePath;
        }

        try {
            // Проверяем наличие GD с поддержкой WebP
            if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
                \Log::warning('GD library with WebP support not available');
                return null;
            }

            $fullPath = Storage::disk('public')->path($filePath);
            $imageInfo = getimagesize($fullPath);
            
            if (!$imageInfo) {
                \Log::warning("Could not get image info for: {$filePath}");
                return null;
            }

            // Создаем изображение из файла
            $sourceImage = null;
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($fullPath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($fullPath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($fullPath);
                    break;
                case IMAGETYPE_WEBP:
                    // Если уже WebP, возвращаем путь
                    return $filePath;
                default:
                    \Log::warning("Unsupported image type for: {$filePath}");
                    return null;
            }

            if (!$sourceImage) {
                \Log::error("Could not create image resource from: {$filePath}");
                return null;
            }

            $originalWidth = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);

            // Вычисляем новые размеры с сохранением пропорций
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);

            // Если изображение меньше максимального размера, не увеличиваем его
            if ($newWidth > $originalWidth || $newHeight > $originalHeight) {
                $newWidth = $originalWidth;
                $newHeight = $originalHeight;
            }

            // Ресайзим изображение
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Сохраняем прозрачность для PNG
            if ($imageInfo[2] == IMAGETYPE_PNG) {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefill($resizedImage, 0, 0, $transparent);
            }

            imagecopyresampled(
                $resizedImage,
                $sourceImage,
                0, 0, 0, 0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            // Создаем WebP версию
            $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $filePath);
            $webpFullPath = Storage::disk('public')->path($webpPath);

            if (!imagewebp($resizedImage, $webpFullPath, $quality)) {
                \Log::error("Failed to create WebP image: {$webpPath}");
                imagedestroy($sourceImage);
                imagedestroy($resizedImage);
                return null;
            }

            // Удаляем оригинальный файл (PNG/JPEG/GIF)
            Storage::disk('public')->delete($filePath);

            // Освобождаем память
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);

            \Log::info("Image converted to WebP: {$filePath} -> {$webpPath}");
            return $webpPath;

        } catch (\Exception $e) {
            \Log::error('Image conversion error: ' . $e->getMessage(), [
                'file' => $filePath,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Оптимизирует изображение: ресайз и конвертация в WebP
     * УСТАРЕВШИЙ МЕТОД - используйте convertToWebP() вместо этого
     *
     * @param string $filePath Полный путь к файлу
     * @param int $maxWidth Максимальная ширина
     * @param int $maxHeight Максимальная высота
     * @param int $quality Качество (0-100)
     * @return array Массив с путями к оригиналу и WebP версии
     * @deprecated Используйте convertToWebP() для автоматической конвертации
     */
    public static function optimizeAndConvertToWebP(
        string $filePath,
        int $maxWidth = 280,
        int $maxHeight = 280,
        int $quality = 85
    ): array {
        $webpPath = self::convertToWebP($filePath, $maxWidth, $maxHeight, $quality);
        
        return [
            'original' => $filePath,
            'webp' => $webpPath
        ];
    }

    /**
     * Получает WebP версию изображения, если она существует
     *
     * @param string $originalPath Путь к оригинальному изображению
     * @return string|null Путь к WebP версии или null
     */
    public static function getWebPPath(string $originalPath): ?string
    {
        $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $originalPath);
        
        if (Storage::disk('public')->exists($webpPath)) {
            return $webpPath;
        }

        return null;
    }
}

