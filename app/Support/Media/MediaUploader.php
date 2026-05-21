<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploader
{
    /**
     * Move a processed file to storage and return its relative path.
     */
    public function upload(string $tempPath, string $directory, string $disk): string
    {
        $extension = pathinfo($tempPath, PATHINFO_EXTENSION) ?: 'bin';
        $filename = Str::uuid().'.'.$extension;
        $path = trim($directory, '/').'/'.$filename;

        $stream = fopen($tempPath, 'rb');
        Storage::disk($disk)->put($path, $stream);
        fclose($stream);

        if (is_file($tempPath)) {
            unlink($tempPath);
        }

        return $path;
    }
}
