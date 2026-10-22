<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Category $category;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->category = Category::create(['name' => 'Peripherals', 'is_active' => true]);
        $this->supplier = Supplier::create(['name' => 'Logitech BD', 'is_active' => true]);
    }

    public function test_admin_can_upload_product_image_on_creation(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('mouse.jpg', 600, 600);

        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Logitech MX Master 3S',
            'sku' => 'SKU-LOGI-001',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 8500,
            'selling_price' => 11500,
            'current_stock' => 15,
            'min_stock_threshold' => 3,
            'image' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'SKU-LOGI-001')->firstOrFail();
        $this->assertNotNull($product->image);
        $this->assertStringStartsWith('products/', $product->image);

        // Verify storage file exists
        Storage::disk('public')->assertExists($product->image);

        // Verify helper
        $this->assertTrue($product->hasCustomImage());
        $this->assertStringContainsString('storage/' . $product->image, $product->image_url);
    }

    public function test_admin_can_replace_existing_product_image(): void
    {
        Storage::fake('public');

        // Create initial product with image
        $initialFile = UploadedFile::fake()->image('initial.jpg', 500, 500);
        $initialPath = $initialFile->store('products', 'public');

        $product = Product::create([
            'name' => 'Wireless Keyboard',
            'sku' => 'SKU-KB-001',
            'category_id' => $this->category->id,
            'purchase_price' => 2000,
            'selling_price' => 2800,
            'current_stock' => 10,
            'min_stock_threshold' => 2,
            'image' => $initialPath,
            'is_active' => true,
        ]);

        Storage::disk('public')->assertExists($initialPath);

        // Replace with new image
        $newFile = UploadedFile::fake()->image('replacement.webp', 800, 800);

        $response = $this->actingAs($this->admin)->put(route('products.update', $product), [
            'name' => 'Wireless Keyboard Pro',
            'sku' => 'SKU-KB-001',
            'category_id' => $this->category->id,
            'purchase_price' => 2200,
            'selling_price' => 3000,
            'min_stock_threshold' => 2,
            'image' => $newFile,
        ]);

        $response->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertNotEquals($initialPath, $product->image);
        $this->assertNotNull($product->image);

        // Old file must be deleted from storage
        Storage::disk('public')->assertMissing($initialPath);

        // New file must exist in storage
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_remove_product_image_without_affecting_other_fields(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('to-remove.png', 400, 400);
        $path = $file->store('products', 'public');

        $product = Product::create([
            'name' => 'Desk Mat Large',
            'sku' => 'SKU-MAT-001',
            'category_id' => $this->category->id,
            'purchase_price' => 400,
            'selling_price' => 700,
            'current_stock' => 20,
            'min_stock_threshold' => 5,
            'image' => $path,
            'is_active' => true,
        ]);

        Storage::disk('public')->assertExists($path);

        // Submit update with remove_image = 1
        $response = $this->actingAs($this->admin)->put(route('products.update', $product), [
            'name' => 'Desk Mat Large (Updated)',
            'sku' => 'SKU-MAT-001',
            'category_id' => $this->category->id,
            'purchase_price' => 400,
            'selling_price' => 700,
            'min_stock_threshold' => 5,
            'remove_image' => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertNull($product->image);
        $this->assertFalse($product->hasCustomImage());

        // File must be deleted from storage
        Storage::disk('public')->assertMissing($path);

        // Image URL should fall back to neutral placeholder
        $this->assertStringContainsString('images/product-placeholder.svg', $product->image_url);
    }

    public function test_image_remains_available_after_editing_product_without_reuploading(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('preserve.jpg', 400, 400);
        $path = $file->store('products', 'public');

        $product = Product::create([
            'name' => 'Webcam HD 1080p',
            'sku' => 'SKU-CAM-001',
            'category_id' => $this->category->id,
            'purchase_price' => 2500,
            'selling_price' => 3500,
            'current_stock' => 8,
            'min_stock_threshold' => 2,
            'image' => $path,
            'is_active' => true,
        ]);

        // Update product without providing a new image or remove_image
        $response = $this->actingAs($this->admin)->put(route('products.update', $product), [
            'name' => 'Webcam HD 1080p Pro Edition',
            'sku' => 'SKU-CAM-001',
            'category_id' => $this->category->id,
            'purchase_price' => 2600,
            'selling_price' => 3800,
            'min_stock_threshold' => 2,
        ]);

        $response->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertEquals($path, $product->image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_deleting_product_deletes_its_image_from_storage(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('delete-me.jpg', 400, 400);
        $path = $file->store('products', 'public');

        $product = Product::create([
            'name' => 'Obsolete Cable',
            'sku' => 'SKU-OBS-001',
            'category_id' => $this->category->id,
            'purchase_price' => 50,
            'selling_price' => 100,
            'current_stock' => 0,
            'min_stock_threshold' => 1,
            'image' => $path,
            'is_active' => true,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        // File must be deleted from storage
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invalid_image_type_is_rejected_by_server_validation(): void
    {
        $nonImageFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Invalid Product',
            'sku' => 'SKU-INV-001',
            'category_id' => $this->category->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'current_stock' => 5,
            'min_stock_threshold' => 1,
            'image' => $nonImageFile,
        ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_oversized_image_is_rejected_by_server_validation(): void
    {
        // 3000 KB exceeds 2048 KB limit
        $oversizedFile = UploadedFile::fake()->image('huge.jpg')->size(3000);

        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Huge Product',
            'sku' => 'SKU-HUGE-001',
            'category_id' => $this->category->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'current_stock' => 5,
            'min_stock_threshold' => 1,
            'image' => $oversizedFile,
        ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_staff_cannot_upload_or_modify_product_images(): void
    {
        $file = UploadedFile::fake()->image('staff.jpg', 300, 300);

        // Staff attempting to store a product
        $response = $this->actingAs($this->staff)->post(route('products.store'), [
            'name' => 'Unauthorized Product',
            'sku' => 'SKU-UNAUTH-001',
            'category_id' => $this->category->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'current_stock' => 5,
            'min_stock_threshold' => 1,
            'image' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_neutral_placeholder_is_returned_when_product_has_no_image(): void
    {
        $product = Product::create([
            'name' => 'Simple Pencil',
            'sku' => 'SKU-PEN-999',
            'category_id' => $this->category->id,
            'purchase_price' => 10,
            'selling_price' => 20,
            'current_stock' => 50,
            'min_stock_threshold' => 10,
            'image' => null,
            'is_active' => true,
        ]);

        $this->assertFalse($product->hasCustomImage());
        $this->assertStringContainsString('images/product-placeholder.svg', $product->image_url);
    }
}
