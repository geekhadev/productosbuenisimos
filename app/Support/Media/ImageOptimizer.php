<?php

namespace App\Support\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Reduce file size when above the threshold without changing dimensions.
     *
     * @return string Absolute path to a temporary processed file.
     */
    public static function optimize(UploadedFile $file, int $maxBytes): string
    {
        $tempPath = self::storeUploadTemporarily($file);

        if (filesize($tempPath) <= $maxBytes) {
            return $tempPath;
        }

        $quality = (int) config('media.image.webp_quality', 85);
        $minQuality = (int) config('media.image.webp_quality_min', 40);
        $manager = ImageManagerFactory::make();
        $image = $manager->read($tempPath);
        $optimizedPath = self::tempPath('webp');

        do {
            $image->toWebp($quality)->save($optimizedPath);

            if (filesize($optimizedPath) <= $maxBytes || $quality <= $minQuality) {
                File::delete($tempPath);

                return $optimizedPath;
            }

            $quality -= 10;
        } while ($quality >= $minQuality);

        File::delete($tempPath);

        return $optimizedPath;
    }

    public static function optimizePath(string $path, int $maxBytes): string
    {
        if (filesize($path) <= $maxBytes) {
            return $path;
        }

        $quality = (int) config('media.image.webp_quality', 85);
        $minQuality = (int) config('media.image.webp_quality_min', 40);
        $manager = ImageManagerFactory::make();
        $image = $manager->read($path);
        $optimizedPath = self::tempPath('webp');

        do {
            $image->toWebp($quality)->save($optimizedPath);

            if (filesize($optimizedPath) <= $maxBytes || $quality <= $minQuality) {
                if ($optimizedPath !== $path) {
                    File::delete($path);
                }

                return $optimizedPath;
            }

            $quality -= 10;
        } while ($quality >= $minQuality);

        if ($optimizedPath !== $path) {
            File::delete($path);
        }

        return $optimizedPath;
    }

    private static function storeUploadTemporarily(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $tempPath = self::tempPath($extension);
        $file->move(dirname($tempPath), basename($tempPath));

        return $tempPath;
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
