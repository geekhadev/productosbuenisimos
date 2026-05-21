<?php

use App\Support\Media\ImageManagerFactory;
use App\Support\Media\MediaUploader;
use Illuminate\Support\Facades\Storage;

test('MediaUploader stores file and returns relative path', function () {
    Storage::fake('public');

    $tempPath = storage_path('app/temp/test-upload-'.str()->uuid().'.webp');
    $directory = dirname($tempPath);

    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    ImageManagerFactory::make()
        ->create(200, 200)
        ->toWebp(80)
        ->save($tempPath);

    $uploader = new MediaUploader;
    $path = $uploader->upload($tempPath, 'products/demo/images', 'public');

    expect($path)->toStartWith('products/demo/images/')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});
