<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PaketUmroh;
use App\Models\Pelanggan;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendaftaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelanggan_can_register_available_package_and_quota_is_reduced(): void
    {
        $user = $this->createPelanggan();
        $paket = PaketUmroh::factory()->create(['sisa_kuota' => 5, 'kuota_jamaah' => 5, 'harga' => 25000000]);

        $response = $this->actingAs($user)->post(route('pendaftaran.store', $paket), [
            'jumlah_jamaah' => 2,
            'catatan_pelanggan' => 'Dua kamar terpisah.',
        ]);

        $pendaftaran = Pendaftaran::firstOrFail();
        $response->assertRedirect(route('pendaftaran.show', $pendaftaran));
        $this->assertDatabaseHas('pendaftarans', [
            'user_id' => $user->id,
            'paket_umroh_id' => $paket->id,
            'jumlah_jamaah' => 2,
            'total_harga' => 50000000,
            'status_pendaftaran' => Pendaftaran::STATUS_MENUNGGU_DP,
        ]);
        $this->assertDatabaseHas('paket_umroh', ['id' => $paket->id, 'sisa_kuota' => 3]);
    }

    public function test_registration_rejects_invalid_or_unavailable_quota(): void
    {
        $user = $this->createPelanggan();
        $paket = PaketUmroh::factory()->create(['sisa_kuota' => 2, 'kuota_jamaah' => 2]);

        $this->actingAs($user)->post(route('pendaftaran.store', $paket), ['jumlah_jamaah' => 0])
            ->assertSessionHasErrors('jumlah_jamaah');
        $this->actingAs($user)->post(route('pendaftaran.store', $paket), ['jumlah_jamaah' => 3])
            ->assertUnprocessable();

        $ditutup = PaketUmroh::factory()->create(['status_paket' => PaketUmroh::STATUS_DITUTUP]);
        $this->actingAs($user)->get(route('pendaftaran.create', $ditutup))->assertNotFound();
        $this->actingAs($user)->post(route('pendaftaran.store', $ditutup), ['jumlah_jamaah' => 1])
            ->assertUnprocessable();

        $this->assertDatabaseCount('pendaftarans', 0);
        $this->assertDatabaseHas('paket_umroh', ['id' => $paket->id, 'sisa_kuota' => 2]);
    }

    public function test_pelanggan_only_sees_and_accesses_own_registrations(): void
    {
        $owner = $this->createPelanggan();
        $other = $this->createPelanggan();
        $owned = Pendaftaran::factory()->create(['user_id' => $owner->id, 'kode_pendaftaran' => 'DFT-OWN']);
        $foreign = Pendaftaran::factory()->create(['user_id' => $other->id, 'kode_pendaftaran' => 'DFT-OTHER']);

        $this->actingAs($owner)->get(route('pendaftaran.index'))
            ->assertOk()->assertSee('DFT-OWN')->assertDontSee('DFT-OTHER');
        $this->actingAs($owner)->get(route('pendaftaran.show', $owned))->assertOk();
        $this->actingAs($owner)->get(route('pendaftaran.show', $foreign))->assertForbidden();
        $this->actingAs($owner)->post(route('pendaftaran.cancel', $foreign))->assertForbidden();
    }

    public function test_pelanggan_can_cancel_before_dp_and_quota_is_restored_once(): void
    {
        $user = $this->createPelanggan();
        $paket = PaketUmroh::factory()->create([
            'kuota_jamaah' => 5,
            'sisa_kuota' => 0,
            'status_paket' => PaketUmroh::STATUS_PENUH,
        ]);
        $pendaftaran = Pendaftaran::factory()->create([
            'user_id' => $user->id,
            'paket_umroh_id' => $paket->id,
            'jumlah_jamaah' => 2,
        ]);

        $this->actingAs($user)->post(route('pendaftaran.cancel', $pendaftaran))->assertRedirect();
        $this->assertDatabaseHas('pendaftarans', ['id' => $pendaftaran->id, 'status_pendaftaran' => Pendaftaran::STATUS_DIBATALKAN]);
        $this->assertDatabaseHas('paket_umroh', ['id' => $paket->id, 'sisa_kuota' => 2, 'status_paket' => PaketUmroh::STATUS_TERSEDIA]);

        $this->actingAs($user)->post(route('pendaftaran.cancel', $pendaftaran))->assertUnprocessable();
        $this->assertDatabaseHas('paket_umroh', ['id' => $paket->id, 'sisa_kuota' => 2]);
    }

    public function test_guest_and_roles_are_restricted_and_admin_can_view_all(): void
    {
        $paket = PaketUmroh::factory()->create();
        $pendaftaran = Pendaftaran::factory()->create();
        $pelanggan = $this->createPelanggan();
        $admin = $this->createAdmin();

        $this->get(route('pendaftaran.index'))->assertRedirect(route('login'));
        $this->get(route('pendaftaran.create', $paket))->assertRedirect(route('login'));
        $this->actingAs($admin)->get(route('pendaftaran.index'))->assertForbidden();
        $this->actingAs($pelanggan)->get(route('admin.pendaftaran.index'))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.pendaftaran.index'))
            ->assertOk()->assertSee($pendaftaran->kode_pendaftaran);
        $this->actingAs($admin)->get(route('admin.pendaftaran.show', $pendaftaran))
            ->assertOk()->assertSee($pendaftaran->kode_pendaftaran);
    }

    public function test_registration_forms_include_csrf_tokens(): void
    {
        $user = $this->createPelanggan();
        $paket = PaketUmroh::factory()->create();
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('pendaftaran.create', $paket))
            ->assertOk()->assertSee('name="_token"', false);
        $this->actingAs($user)->get(route('pendaftaran.show', $pendaftaran))
            ->assertOk()->assertSee('name="_token"', false);
    }

    private function createPelanggan(): User
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        Pelanggan::create(['user_id' => $user->id]);

        return $user;
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create(['role' => 'admin']);
        Admin::create(['user_id' => $user->id, 'jabatan' => 'Administrator']);

        return $user;
    }
}
