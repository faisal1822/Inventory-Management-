<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_addition_adjustment_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['name' => 'Tools', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Hammer',
            'sku' => 'SKU-HAM-001',
            'category_id' => $category->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'current_stock' => 10,
            'min_stock_threshold' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('adjustments.store'), [
            'product_id' => $product->id,
            'type' => 'addition',
            'quantity_change' => 5,
            'reason' => 'opening_stock',
            'notes' => 'Found extra hammer stock in storage',
        ]);

        $response->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertEquals(15, $product->current_stock);

        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'type' => 'addition',
            'previous_quantity' => 10,
            'quantity_change' => 5,
            'new_quantity' => 15,
            'reason' => 'opening_stock',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'previous_stock' => 10,
            'quantity_change' => 5,
            'new_stock' => 15,
        ]);
    }

    public function test_stock_subtraction_adjustment_prevents_negative_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['name' => 'Tools', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Pliers',
            'sku' => 'SKU-PLI-001',
            'category_id' => $category->id,
            'purchase_price' => 80,
            'selling_price' => 120,
            'current_stock' => 3, // only 3
            'min_stock_threshold' => 1,
            'is_active' => true,
        ]);

        // Attempt to deduct 5 units
        $response = $this->actingAs($admin)->post(route('adjustments.store'), [
            'product_id' => $product->id,
            'type' => 'subtraction',
            'quantity_change' => 5,
            'reason' => 'damaged',
            'notes' => 'Damaged items',
        ]);

        $response->assertSessionHas('error');
        $product->refresh();
        $this->assertEquals(3, $product->current_stock);
        $this->assertEquals(0, StockAdjustment::count());
    }
}
