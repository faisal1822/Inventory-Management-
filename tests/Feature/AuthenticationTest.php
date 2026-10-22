<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@stockmaster.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@stockmaster.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@stockmaster.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'testuser@stockmaster.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@stockmaster.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@stockmaster.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
