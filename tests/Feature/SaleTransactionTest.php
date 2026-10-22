<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_creation_decreases_stock_and_records_movement(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $category = Category::create(['name' => 'Stationery', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Executive Pen',
            'sku' => 'SKU-PEN-001',
            'category_id' => $category->id,
            'purchase_price' => 50.00,
            'selling_price' => 120.00,
            'current_stock' => 15,
            'min_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $saleData = [
            'sale_date' => date('Y-m-d'),
            'customer_name' => 'Alice Rahman',
            'customer_phone' => '+880 1711 999888',
            'payment_method' => 'cash',
            'discount' => 20.00,
            'paid_amount' => 340.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => 120.00,
                ],
            ],
        ];

        $response = $this->actingAs($staff)->post(route('sales.store'), $saleData);

        $response->assertSessionHasNoErrors();

        // 3 items * 120 = 360 subtotal - 20 discount = 340 grand total
        $this->assertDatabaseHas('sales', [
            'customer_name' => 'Alice Rahman',
            'total_quantity' => 3,
            'subtotal' => 360.00,
            'discount' => 20.00,
            'grand_total' => 340.00,
            'paid_amount' => 340.00,
            'payment_method' => 'cash',
        ]);

        // Stock decreased from 15 to 12
        $product->refresh();
        $this->assertEquals(12, $product->current_stock);

        // Stock movement recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'previous_stock' => 15,
            'quantity_change' => -3,
            'new_stock' => 12,
        ]);

        // SaleItem stored with cost basis (50.00)
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 120.00,
            'unit_cost' => 50.00,
            'line_total' => 360.00,
        ]);
    }

    public function test_sale_fails_when_stock_is_insufficient(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $category = Category::create(['name' => 'Electronics', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Flash Drive 64GB',
            'sku' => 'SKU-USB-064',
            'category_id' => $category->id,
            'purchase_price' => 300.00,
            'selling_price' => 450.00,
            'current_stock' => 2, // only 2 in stock
            'min_stock_threshold' => 5,
            'is_active' => true,
        ]);

        // Attempt to sell 5 units
        $saleData = [
            'sale_date' => date('Y-m-d'),
            'customer_name' => 'Bob',
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 450.00,
                ],
            ],
        ];

        $response = $this->actingAs($staff)->post(route('sales.store'), $saleData);

        // Must redirect back with error
        $response->assertSessionHas('error');

        // Stock must remain unchanged at 2
        $product->refresh();
        $this->assertEquals(2, $product->current_stock);

        // No sale created
        $this->assertEquals(0, Sale::count());
    }
}
