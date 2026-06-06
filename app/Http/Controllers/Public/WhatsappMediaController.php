<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Stock\ProductMedia;
use App\Support\Media\ImageManagerFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class WhatsappMediaController extends Controller
{
    /**
     * Serves a product image as JPEG for WhatsApp (which does not support WebP).
     */
    public function show(string $mediaId): Response
    {
        $media = ProductMedia::query()->whereKey($mediaId)->firstOrFail();

        $content = Storage::disk($media->disk)->get($media->path);

        if ($content === null) {
            abort(404);
        }

        $jpeg = ImageManagerFactory::make()
            ->read($content)
            ->toJpeg(quality: 85);

        return response((string) $jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
