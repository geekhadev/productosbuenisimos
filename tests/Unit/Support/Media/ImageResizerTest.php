<?php

use App\Support\Media\ImageManagerFactory;
use App\Support\Media\ImageResizer;

test('ImageResizer scales down large image preserving aspect ratio', function () {
    $sourcePath = storage_path('app/temp/test-resize-'.str()->uuid().'.jpg');
    $directory = dirname($sourcePath);

    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $manager = ImageManagerFactory::make();
    $manager->create(3000, 2000)->toJpeg(90)->save($sourcePath);

    $resultPath = ImageResizer::resize($sourcePath, 2048);
    $result = $manager->read($resultPath);

    expect($result->width())->toBe(2048)
        ->and($result->height())->toBe(1365);

    if (is_file($resultPath)) {
        unlink($resultPath);
    }
});
