<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_csv_export_streams_correct_headers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('reports.sales', ['export' => 'csv']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_inventory_valuation_report_is_accessible(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('reports.inventory'));

        $response->assertStatus(200);
        $response->assertSee('Inventory Valuation');
    }

    public function test_admin_can_update_business_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('settings.update'), [
            'business_name' => 'Mega Mart Retail Ltd',
            'business_address' => 'Mirpur-10, Dhaka',
            'phone' => '+880 1722 999888',
            'email' => 'contact@megamart.bd',
            'currency_symbol' => '৳',
            'tax_percentage' => '5.00',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('Mega Mart Retail Ltd', Setting::get('business_name'));
        $this->assertEquals('+880 1722 999888', Setting::get('phone'));
    }
}
