<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAndCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_cannot_be_deleted_if_it_has_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['name' => 'Groceries', 'is_active' => true]);

        Product::create([
            'name' => 'Rice Bag 5kg',
            'sku' => 'SKU-RICE-005',
            'category_id' => $category->id,
            'purchase_price' => 300,
            'selling_price' => 380,
            'current_stock' => 10,
            'min_stock_threshold' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_supplier_cannot_be_deleted_if_it_has_purchases(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $supplier = Supplier::create(['name' => 'Apex Foods', 'is_active' => true]);

        // Create purchase for supplier
        $supplier->purchases()->create([
            'reference_no' => 'PO-TEST-0001',
            'purchase_date' => date('Y-m-d'),
            'total_quantity' => 1,
            'subtotal' => 100,
            'grand_total' => 100,
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('suppliers.destroy', $supplier));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_product_cannot_be_created_with_duplicate_sku(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['name' => 'General', 'is_active' => true]);

        Product::create([
            'name' => 'Original Item',
            'sku' => 'SKU-DUP-001',
            'category_id' => $category->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'current_stock' => 5,
            'min_stock_threshold' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Duplicate Item',
            'sku' => 'SKU-DUP-001', // duplicate
            'category_id' => $category->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'current_stock' => 5,
            'min_stock_threshold' => 1,
        ]);

        $response->assertSessionHasErrors('sku');
    }
}
