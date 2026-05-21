<?php

use App\Support\Media\ImageManagerFactory;
use App\Support\Media\ImageOptimizer;
use Illuminate\Http\UploadedFile;

test('ImageOptimizer reduces file size above threshold', function () {
    $sourcePath = storage_path('app/temp/test-optimize-'.str()->uuid().'.jpg');
    $directory = dirname($sourcePath);

    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $manager = ImageManagerFactory::make();
    $manager->create(4000, 3000)->toJpeg(100)->save($sourcePath);

    $maxBytes = 200 * 1024;

    expect(filesize($sourcePath))->toBeGreaterThan($maxBytes);

    $file = new UploadedFile(
        $sourcePath,
        'large.jpg',
        'image/jpeg',
        null,
        true,
    );

    $optimizedPath = ImageOptimizer::optimize($file, $maxBytes);

    expect(filesize($optimizedPath))->toBeLessThan($maxBytes);

    if (is_file($optimizedPath)) {
        unlink($optimizedPath);
    }
});
