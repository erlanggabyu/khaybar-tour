<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_pelanggan_can_register_and_is_routed_to_pelanggan_dashboard(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan.baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'no_hp' => '081234567891',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'pelanggan.baru@example.com',
            'role' => 'pelanggan',
        ]);
        $this->assertDatabaseHas('pelanggans', [
            'user_id' => User::where('email', 'pelanggan.baru@example.com')->value('id'),
        ]);

        $this->get(route('dashboard'))
            ->assertRedirect(route('pelanggan.dashboard'));
    }

    public function test_pelanggan_can_login_and_is_routed_to_pelanggan_dashboard(): void
    {
        $user = $this->createPelanggan();

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))
            ->assertRedirect(route('pelanggan.dashboard'));
    }

    public function test_admin_can_login_and_is_routed_to_admin_dashboard(): void
    {
        $user = $this->createAdmin();

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_pelanggan_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->createPelanggan())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_pelanggan_dashboard(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('pelanggan.dashboard'))
            ->assertForbidden();
    }

    public function test_logout_invalidates_authenticated_session(): void
    {
        $this->actingAs($this->createPelanggan())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_post_forms_render_laravel_csrf_tokens(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="_token"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="_token"', false);

        $this->actingAs($this->createPelanggan())
            ->get(route('pelanggan.dashboard'))
            ->assertOk()
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('name="_token"', false);
    }

    private function createPelanggan(): User
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
        ]);

        Pelanggan::create([
            'user_id' => $user->id,
        ]);

        return $user;
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        Admin::create([
            'user_id' => $user->id,
            'jabatan' => 'Administrator',
        ]);

        return $user;
    }
}
