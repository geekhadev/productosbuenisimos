<?php

use App\Enums\Stock\ProductMediaType;
use App\Models\Company;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use App\Models\User;
use App\Support\Media\ImageManagerFactory;
use Database\Seeders\Administration\PermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    Storage::fake('public');
    config([
        'media.disk' => 'public',
        'media.image.max_bytes' => 2 * 1024 * 1024,
        'media.image.max_side_px' => 2048,
    ]);
});

function productMediaFixture(): array
{
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $product = Product::factory()->for($company)->create(['is_active' => true]);

    return compact('company', 'user', 'product');
}

test('uploading a valid image creates database record and stores file', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.images.store', $product), [
            'images' => [
                UploadedFile::fake()->image('photo.jpg', 800, 600)->size(500),
            ],
        ]);

    $response->assertSuccessful();
    $response->assertJsonPath('images.0.original_name', 'photo.jpg');
    expect($response->json('images.0.url'))->toStartWith('/storage/');

    $media = ProductMedia::query()->where('product_id', $product->id)->first();
    expect($media)->not->toBeNull()
        ->and($media->type)->toBe(ProductMediaType::Image)
        ->and(Storage::disk('public')->exists($media->path))->toBeTrue();
});

test('uploading an oversized image stores a smaller optimized file', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.images.store', $product), [
            'images' => [
                UploadedFile::fake()->image('large.jpg', 4000, 3000)->size(4500),
            ],
        ]);

    $response->assertSuccessful();

    $media = ProductMedia::query()->where('product_id', $product->id)->first();
    expect($media)->not->toBeNull()
        ->and($media->size)->toBeLessThan(2 * 1024 * 1024);
});

test('uploading a large image respects max side configuration', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.images.store', $product), [
            'images' => [
                UploadedFile::fake()->image('wide.jpg', 3000, 2000),
            ],
        ])
        ->assertSuccessful();

    $path = ProductMedia::query()->where('product_id', $product->id)->value('path');
    $storedPath = Storage::disk('public')->path($path);
    $image = ImageManagerFactory::make()->read($storedPath);

    expect($image->width())->toBeLessThanOrEqual(2048)
        ->and($image->height())->toBeLessThanOrEqual(2048);
});

test('uploading a sixth image returns validation error', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    ProductMedia::factory()
        ->count(5)
        ->for($product)
        ->sequence(fn ($sequence) => ['sort_order' => $sequence->index])
        ->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.images.store', $product), [
            'images' => [
                UploadedFile::fake()->image('extra.jpg'),
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images');
});

test('reordering images updates sort_order in database', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    $first = ProductMedia::factory()->for($product)->create(['sort_order' => 0]);
    $second = ProductMedia::factory()->for($product)->create(['sort_order' => 1]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->putJson(route('stock.products.images.reorder', $product), [
            'order' => [$second->id, $first->id],
        ])
        ->assertSuccessful();

    expect($first->fresh()->sort_order)->toBe(1)
        ->and($second->fresh()->sort_order)->toBe(0);
});

test('deleting an image removes database record and file', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    $media = ProductMedia::factory()->for($product)->create();
    Storage::disk('public')->put($media->path, 'binary');

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->deleteJson(route('stock.products.images.destroy', [$product, $media]))
        ->assertSuccessful();

    expect(ProductMedia::query()->find($media->id))->toBeNull()
        ->and(Storage::disk('public')->exists($media->path))->toBeFalse();
});

test('invalid php upload for video returns a descriptive validation error', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    $path = storage_path('app/temp/test-video.mp4');
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, 'fake-video');

    $invalidUpload = new UploadedFile(
        $path,
        'clip.mp4',
        'video/mp4',
        UPLOAD_ERR_INI_SIZE,
        false,
    );

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.video.store', $product), [
            'video' => $invalidUpload,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('video');

    expect($response->json('errors.video.0'))
        ->toContain('límite de subida del servidor')
        ->toContain('200 MB');

    @unlink($path);
});

test('uploading a new video replaces the previous one', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    $previous = ProductMedia::factory()->for($product)->create([
        'type' => ProductMediaType::Video,
        'path' => 'products/'.$product->id.'/videos/old.mp4',
        'mime_type' => 'video/mp4',
        'original_name' => 'old.mp4',
    ]);
    Storage::disk('public')->put($previous->path, 'old-video');

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.video.store', $product), [
            'video' => UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4'),
        ])
        ->assertSuccessful();

    expect(ProductMedia::query()->where('product_id', $product->id)->where('type', ProductMediaType::Video)->count())
        ->toBe(1)
        ->and(Storage::disk('public')->exists($previous->path))->toBeFalse();
});

test('uploading a file with invalid real mime returns 422', function () {
    ['company' => $company, 'user' => $user, 'product' => $product] = productMediaFixture();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.images.store', $product), [
            'images' => [
                UploadedFile::fake()->create('fake.jpg', 100, 'text/plain'),
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images.0');
});

test('user without update permission cannot upload images', function () {
    ['company' => $company, 'product' => $product] = productMediaFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.images.store', $product), [
            'images' => [
                UploadedFile::fake()->image('photo.jpg'),
            ],
        ])
        ->assertForbidden();
});

test('inactive product cannot receive new images', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $product = Product::factory()->for($company)->create(['is_active' => false]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->postJson(route('stock.products.images.store', $product), [
            'images' => [
                UploadedFile::fake()->image('photo.jpg'),
            ],
        ])
        ->assertForbidden();
});
