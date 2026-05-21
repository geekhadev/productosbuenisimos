<?php

namespace App\Actions\Stock\Products;

use App\Enums\Stock\ProductMediaType;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use App\Support\Media\MediaUploader;
use App\Support\Stock\ProductMediaPayload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UploadProductVideoAction
{
    /**
     * @return array{images: list<array<string, mixed>>, video: array<string, mixed>|null}
     */
    public function execute(Product $product, UploadedFile $file): array
    {
        $disk = (string) config('media.disk', 'public');
        $uploader = new MediaUploader;
        $extension = $this->extensionForMime($file->getMimeType() ?? '');
        $tempPath = $this->storeTemporarily($file, $extension);

        return DB::transaction(function () use ($product, $file, $disk, $uploader, $tempPath): array {
            $existing = $product->media()
                ->where('type', ProductMediaType::Video)
                ->first();

            if ($existing !== null) {
                if (Storage::disk($existing->disk)->exists($existing->path)) {
                    Storage::disk($existing->disk)->delete($existing->path);
                }

                $existing->delete();
            }

            $storedPath = $uploader->upload(
                $tempPath,
                'products/'.$product->id.'/videos',
                $disk,
            );

            $size = (int) Storage::disk($disk)->size($storedPath);

            ProductMedia::query()->create([
                'product_id' => $product->id,
                'type' => ProductMediaType::Video,
                'path' => $storedPath,
                'sort_order' => 0,
                'disk' => $disk,
                'mime_type' => $file->getMimeType() ?? 'video/mp4',
                'size' => $size,
                'original_name' => $file->getClientOriginalName(),
            ]);

            $product->load('media');

            return ProductMediaPayload::forProduct($product);
        });
    }

    private function storeTemporarily(UploadedFile $file, string $extension): string
    {
        $directory = storage_path('app/temp/media');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $tempPath = $directory.'/'.str()->uuid().'.'.$extension;
        $file->move(dirname($tempPath), basename($tempPath));

        return $tempPath;
    }

    private function extensionForMime(string $mime): string
    {
        return match ($mime) {
            'video/webm' => 'webm',
            'video/quicktime' => 'mov',
            default => 'mp4',
        };
    }
}
