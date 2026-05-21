<?php

return [

    'disk' => env('MEDIA_DISK', 'public'),

    'image' => [
        'max_bytes' => (int) env('MEDIA_IMAGE_MAX_BYTES', 2 * 1024 * 1024),
        'max_side_px' => (int) env('MEDIA_IMAGE_MAX_SIDE_PX', 2048),
        'webp_quality' => (int) env('MEDIA_IMAGE_WEBP_QUALITY', 85),
        'webp_quality_min' => (int) env('MEDIA_IMAGE_WEBP_QUALITY_MIN', 40),
    ],

];
