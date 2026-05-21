<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageResizer
{
    /**
     * Resize so no side exceeds $maxSide while preserving aspect ratio.
     *
     * @return string Absolute path to a temporary processed file.
     */
    public static function resize(string $tempPath, int $maxSide): string
    {
        $manager = ImageManagerFactory::make();
        $image = $manager->read($tempPath);
        $width = $image->width();
        $height = $image->height();

        if ($width <= $maxSide && $height <= $maxSide) {
            return $tempPath;
        }

        $image->scaleDown($maxSide, $maxSide);

        $resizedPath = self::tempPath(pathinfo($tempPath, PATHINFO_EXTENSION) ?: 'webp');
        $image->save($resizedPath);

        if ($resizedPath !== $tempPath) {
            File::delete($tempPath);
        }

        return $resizedPath;
    }

    /**
     * Encode the image at the given path as WebP.
     *
     * @return string Absolute path to a temporary WebP file.
     */
    public static function toWebp(string $tempPath): string
    {
        $manager = ImageManagerFactory::make();
        $webpPath = self::tempPath('webp');
        $quality = (int) config('media.image.webp_quality', 85);
        $manager->read($tempPath)->toWebp($quality)->save($webpPath);

        if ($webpPath !== $tempPath) {
            File::delete($tempPath);
        }

        return $webpPath;
    }

    private static function tempPath(string $extension): string
    {
        $directory = storage_path('app/temp/media');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory.'/'.Str::uuid().'.'.$extension;
    }
}
