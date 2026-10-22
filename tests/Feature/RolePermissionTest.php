<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_settings_and_staff_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('categories.index'))
            ->assertStatus(200);
    }

    public function test_staff_cannot_access_settings_or_users_management(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        // Access to settings should be forbidden (403)
        $this->actingAs($staff)
            ->get(route('settings.index'))
            ->assertStatus(403);

        // Access to staff management should be forbidden (403)
        $this->actingAs($staff)
            ->get(route('users.index'))
            ->assertStatus(403);

        // Access to categories should be forbidden (403)
        $this->actingAs($staff)
            ->get(route('categories.index'))
            ->assertStatus(403);
    }

    public function test_staff_can_access_dashboard_products_and_sales(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertStatus(200);

        $this->actingAs($staff)
            ->get(route('products.index'))
            ->assertStatus(200);

        $this->actingAs($staff)
            ->get(route('sales.index'))
            ->assertStatus(200);

        $this->actingAs($staff)
            ->get(route('sales.create'))
            ->assertStatus(200);
    }
}
