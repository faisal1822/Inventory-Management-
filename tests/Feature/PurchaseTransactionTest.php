<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_creation_increases_stock_and_logs_movement(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['name' => 'Hardware', 'is_active' => true]);
        $supplier = Supplier::create(['name' => 'Supplier Tech', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Screwdriver Set',
            'sku' => 'SKU-TOOL-001',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'purchase_price' => 150.00,
            'selling_price' => 250.00,
            'current_stock' => 10,
            'min_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $purchaseData = [
            'supplier_id' => $supplier->id,
            'purchase_date' => date('Y-m-d'),
            'notes' => 'Testing restock',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 20,
                    'unit_cost' => 160.00,
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('purchases.store'), $purchaseData);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplier->id,
            'total_quantity' => 20,
            'grand_total' => 3200.00,
        ]);

        // Stock must have increased from 10 to 30
        $product->refresh();
        $this->assertEquals(30, $product->current_stock);
        $this->assertEquals(160.00, $product->purchase_price);

        // Stock movement must be recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'purchase',
            'previous_stock' => 10,
            'quantity_change' => 20,
            'new_stock' => 30,
        ]);
    }
}
