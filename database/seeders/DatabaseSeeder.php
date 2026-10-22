<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Settings
        $settings = [
            'business_name' => 'StockMaster Pro',
            'business_address' => 'Plot 45, Gulshan Commercial Area, Dhaka-1212',
            'phone' => '+880 1712 345678',
            'email' => 'contact@stockmasterpro.com',
            'currency_symbol' => '৳',
            'tax_percentage' => '0',
            'invoice_prefix' => 'INV-',
            'purchase_prefix' => 'PO-',
            'costing_method' => 'Purchase Cost Basis (WAC)',
        ];

        foreach ($settings as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        // 2. Initial Admin and Staff Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@stockmaster.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '+880 1711 000001',
                'is_active' => true,
            ]
        );

        $staff = User::firstOrCreate(
            ['email' => 'staff@stockmaster.com'],
            [
                'name' => 'Sales Staff',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'phone' => '+880 1711 000002',
                'is_active' => true,
            ]
        );

        // 3. Categories
        $categoriesData = [
            ['name' => 'Electronics & Gadgets', 'description' => 'Laptops, mobile accessories, and electronic components.'],
            ['name' => 'Beverages & Drinks', 'description' => 'Cold drinks, juices, energy drinks, mineral water.'],
            ['name' => 'Packaged Food & Snacks', 'description' => 'Biscuits, noodles, chocolates, confectionery.'],
            ['name' => 'Stationery & Office Supplies', 'description' => 'Pens, paper reams, staplers, registers.'],
            ['name' => 'Hardware & Tools', 'description' => 'Hand tools, cables, switches, fasteners.'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['name']] = Category::firstOrCreate(['name' => $c['name']], [
                'description' => $c['description'],
                'is_active' => true,
            ]);
        }

        // 4. Suppliers
        $suppliersData = [
            [
                'name' => 'Rafiqul Islam',
                'company_name' => 'Bengal Tech Distributors',
                'phone' => '+880 1812 111222',
                'email' => 'sales@bengaltech.bd',
                'address' => 'Motijheel C/A, Dhaka',
                'notes' => 'Primary electronics vendor with 30-day warranty support.',
            ],
            [
                'name' => 'Abdul Karim',
                'company_name' => 'Pran-RFL Consumer Supply',
                'phone' => '+880 1912 333444',
                'email' => 'orders@pranconsumer.bd',
                'address' => 'Gulishtan Wholesale Market, Dhaka',
                'notes' => 'Food and beverages authorized wholesaler.',
            ],
            [
                'name' => 'Tanvir Ahmed',
                'company_name' => 'Delta Stationery Mart',
                'phone' => '+880 1715 555666',
                'email' => 'supply@deltastationery.bd',
                'address' => 'Banglabazar, Dhaka',
                'notes' => 'Paper and office supplies wholesale supplier.',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $s) {
            $suppliers[$s['company_name']] = Supplier::firstOrCreate(['email' => $s['email']], $s);
        }

        // 5. Products
        $productsData = [
            [
                'name' => 'Wireless Bluetooth Mouse M185',
                'sku' => 'SKU-ELEC-001',
                'barcode' => '890123456701',
                'category_id' => $categories['Electronics & Gadgets']->id,
                'supplier_id' => $suppliers['Bengal Tech Distributors']->id,
                'purchase_price' => 650.00,
                'selling_price' => 950.00,
                'current_stock' => 35,
                'min_stock_threshold' => 10,
                'description' => 'Optical 2.4GHz wireless mouse with nano USB receiver.',
            ],
            [
                'name' => 'Mechanical Gaming Keyboard RGB',
                'sku' => 'SKU-ELEC-002',
                'barcode' => '890123456702',
                'category_id' => $categories['Electronics & Gadgets']->id,
                'supplier_id' => $suppliers['Bengal Tech Distributors']->id,
                'purchase_price' => 2200.00,
                'selling_price' => 2950.00,
                'current_stock' => 12,
                'min_stock_threshold' => 5,
                'description' => 'Blue switch tactile mechanical keyboard with customizable RGB backlighting.',
            ],
            [
                'name' => 'USB-C Fast Charging Cable (1.5m)',
                'sku' => 'SKU-ELEC-003',
                'barcode' => '890123456703',
                'category_id' => $categories['Electronics & Gadgets']->id,
                'supplier_id' => $suppliers['Bengal Tech Distributors']->id,
                'purchase_price' => 140.00,
                'selling_price' => 280.00,
                'current_stock' => 4, // Low stock on purpose
                'min_stock_threshold' => 15,
                'description' => 'Braided nylon 60W USB-C to USB-C fast charge cable.',
            ],
            [
                'name' => 'Premium Mango Juice (1 Litre)',
                'sku' => 'SKU-BEV-001',
                'barcode' => '890123456704',
                'category_id' => $categories['Beverages & Drinks']->id,
                'supplier_id' => $suppliers['Pran-RFL Consumer Supply']->id,
                'purchase_price' => 130.00,
                'selling_price' => 165.00,
                'current_stock' => 45,
                'min_stock_threshold' => 12,
                'description' => '100% natural mango pulp juice, chilled ready to serve.',
            ],
            [
                'name' => 'Mineral Spring Water (500ml Pack of 12)',
                'sku' => 'SKU-BEV-002',
                'barcode' => '890123456705',
                'category_id' => $categories['Beverages & Drinks']->id,
                'supplier_id' => $suppliers['Pran-RFL Consumer Supply']->id,
                'purchase_price' => 180.00,
                'selling_price' => 240.00,
                'current_stock' => 28,
                'min_stock_threshold' => 10,
                'description' => 'Pure filtered mineral water 500ml bottles pack.',
            ],
            [
                'name' => 'Crispy Potato Crackers (Pack of 10)',
                'sku' => 'SKU-FOOD-001',
                'barcode' => '890123456706',
                'category_id' => $categories['Packaged Food & Snacks']->id,
                'supplier_id' => $suppliers['Pran-RFL Consumer Supply']->id,
                'purchase_price' => 150.00,
                'selling_price' => 200.00,
                'current_stock' => 2, // Low stock on purpose
                'min_stock_threshold' => 10,
                'description' => 'Light salted crispy potato wafers, export quality.',
            ],
            [
                'name' => 'A4 Copier Paper 80GSM (500 Sheets Ream)',
                'sku' => 'SKU-STAT-001',
                'barcode' => '890123456707',
                'category_id' => $categories['Stationery & Office Supplies']->id,
                'supplier_id' => $suppliers['Delta Stationery Mart']->id,
                'purchase_price' => 380.00,
                'selling_price' => 480.00,
                'current_stock' => 50,
                'min_stock_threshold' => 15,
                'description' => 'High-speed copier paper, ultra-white 80 GSM.',
            ],
            [
                'name' => 'Ballpoint Gel Pen 0.5mm (Box of 20)',
                'sku' => 'SKU-STAT-002',
                'barcode' => '890123456708',
                'category_id' => $categories['Stationery & Office Supplies']->id,
                'supplier_id' => $suppliers['Delta Stationery Mart']->id,
                'purchase_price' => 120.00,
                'selling_price' => 180.00,
                'current_stock' => 22,
                'min_stock_threshold' => 10,
                'description' => 'Smooth flow waterproof black ink gel pen box.',
            ],
        ];

        $products = [];
        foreach ($productsData as $p) {
            $products[$p['sku']] = Product::firstOrCreate(['sku' => $p['sku']], $p);
        }

        // 6. Record Initial Purchase (Receipt)
        $purchase = Purchase::firstOrCreate(
            ['reference_no' => 'PO-' . date('Ymd') . '-0001'],
            [
                'supplier_id' => $suppliers['Bengal Tech Distributors']->id,
                'purchase_date' => Carbon::now()->subDays(3)->toDateString(),
                'total_quantity' => 25,
                'subtotal' => 28500.00,
                'grand_total' => 28500.00,
                'notes' => 'Quarterly electronic gadgets restock order.',
                'user_id' => $admin->id,
            ]
        );

        PurchaseItem::firstOrCreate([
            'purchase_id' => $purchase->id,
            'product_id' => $products['SKU-ELEC-001']->id,
        ], [
            'quantity' => 15,
            'unit_cost' => 650.00,
            'line_total' => 9750.00,
        ]);

        PurchaseItem::firstOrCreate([
            'purchase_id' => $purchase->id,
            'product_id' => $products['SKU-ELEC-002']->id,
        ], [
            'quantity' => 10,
            'unit_cost' => 1875.00,
            'line_total' => 18750.00,
        ]);

        // 7. Record Initial Sales (Invoices)
        $sale1 = Sale::firstOrCreate(
            ['invoice_no' => 'INV-' . date('Ymd') . '-0001'],
            [
                'sale_date' => Carbon::now()->subDays(1)->toDateString(),
                'customer_name' => 'Fahim Rahman',
                'customer_phone' => '+880 1719 888999',
                'total_quantity' => 3,
                'subtotal' => 3900.00,
                'discount' => 100.00,
                'grand_total' => 3800.00,
                'paid_amount' => 3800.00,
                'payment_method' => 'mobile_banking',
                'notes' => 'Paid via bKash Merchant pay.',
                'user_id' => $staff->id,
            ]
        );

        SaleItem::firstOrCreate([
            'sale_id' => $sale1->id,
            'product_id' => $products['SKU-ELEC-001']->id,
        ], [
            'quantity' => 1,
            'unit_price' => 950.00,
            'unit_cost' => 650.00,
            'line_total' => 950.00,
        ]);

        SaleItem::firstOrCreate([
            'sale_id' => $sale1->id,
            'product_id' => $products['SKU-ELEC-002']->id,
        ], [
            'quantity' => 1,
            'unit_price' => 2950.00,
            'unit_cost' => 2200.00,
            'line_total' => 2950.00,
        ]);

        $sale2 = Sale::firstOrCreate(
            ['invoice_no' => 'INV-' . date('Ymd') . '-0002'],
            [
                'sale_date' => Carbon::now()->toDateString(),
                'customer_name' => 'Walk-in Customer',
                'customer_phone' => null,
                'total_quantity' => 4,
                'subtotal' => 1140.00,
                'discount' => 0.00,
                'grand_total' => 1140.00,
                'paid_amount' => 1140.00,
                'payment_method' => 'cash',
                'notes' => 'Counter sales receipt.',
                'user_id' => $staff->id,
            ]
        );

        SaleItem::firstOrCreate([
            'sale_id' => $sale2->id,
            'product_id' => $products['SKU-BEV-001']->id,
        ], [
            'quantity' => 2,
            'unit_price' => 165.00,
            'unit_cost' => 130.00,
            'line_total' => 330.00,
        ]);

        SaleItem::firstOrCreate([
            'sale_id' => $sale2->id,
            'product_id' => $products['SKU-STAT-001']->id,
        ], [
            'quantity' => 1,
            'unit_price' => 480.00,
            'unit_cost' => 380.00,
            'line_total' => 480.00,
        ]);

        SaleItem::firstOrCreate([
            'sale_id' => $sale2->id,
            'product_id' => $products['SKU-BEV-002']->id,
        ], [
            'quantity' => 1,
            'unit_price' => 240.00,
            'unit_cost' => 180.00,
            'line_total' => 240.00,
        ]);

        // 8. Record Initial Stock Adjustment
        $adj = StockAdjustment::firstOrCreate(
            ['adjustment_no' => 'ADJ-' . date('Ymd') . '-0001'],
            [
                'product_id' => $products['SKU-FOOD-001']->id,
                'type' => 'subtraction',
                'previous_quantity' => 4,
                'quantity_change' => 2,
                'new_quantity' => 2,
                'reason' => 'damaged',
                'notes' => 'Water damage during warehouse transport.',
                'user_id' => $admin->id,
            ]
        );

        // 9. Initial Stock Movements
        StockMovement::firstOrCreate(
            ['reference_no' => $purchase->reference_no, 'product_id' => $products['SKU-ELEC-001']->id],
            [
                'type' => 'purchase',
                'reference_id' => $purchase->id,
                'previous_stock' => 20,
                'quantity_change' => 15,
                'new_stock' => 35,
                'user_id' => $admin->id,
                'notes' => 'Purchase intake: ' . $purchase->reference_no,
            ]
        );

        StockMovement::firstOrCreate(
            ['reference_no' => $sale1->invoice_no, 'product_id' => $products['SKU-ELEC-001']->id],
            [
                'type' => 'sale',
                'reference_id' => $sale1->id,
                'previous_stock' => 36,
                'quantity_change' => -1,
                'new_stock' => 35,
                'user_id' => $staff->id,
                'notes' => 'Sold on invoice: ' . $sale1->invoice_no,
            ]
        );

        StockMovement::firstOrCreate(
            ['reference_no' => $adj->adjustment_no, 'product_id' => $products['SKU-FOOD-001']->id],
            [
                'type' => 'adjustment',
                'reference_id' => $adj->id,
                'previous_stock' => 4,
                'quantity_change' => -2,
                'new_stock' => 2,
                'user_id' => $admin->id,
                'notes' => 'Stock adjustment: ' . $adj->reason,
            ]
        );
    }
}
