<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PaketUmroh;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaketUmrohTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_list_only_displays_available_packages(): void
    {
        $tersedia = PaketUmroh::factory()->create([
            'nama_paket' => 'Paket Terlihat',
        ]);
        $ditutup = PaketUmroh::factory()->create([
            'nama_paket' => 'Paket Ditutup',
            'status_paket' => PaketUmroh::STATUS_DITUTUP,
        ]);
        PaketUmroh::factory()->create([
            'nama_paket' => 'Paket Tanpa Kuota',
            'sisa_kuota' => 0,
        ]);

        $this->get(route('pakets.index'))
            ->assertOk()
            ->assertSee($tersedia->nama_paket)
            ->assertDontSee($ditutup->nama_paket)
            ->assertDontSee('Paket Tanpa Kuota');
    }

    public function test_public_can_view_available_package_detail_but_not_closed_package(): void
    {
        $tersedia = PaketUmroh::factory()->create();
        $ditutup = PaketUmroh::factory()->create([
            'status_paket' => PaketUmroh::STATUS_DITUTUP,
        ]);

        $this->get(route('pakets.show', $tersedia))
            ->assertOk()
            ->assertSee($tersedia->nama_paket);

        $this->get(route('pakets.show', $ditutup))
            ->assertNotFound();
    }

    public function test_admin_can_create_and_update_package(): void
    {
        $admin = $this->createAdmin();
        $data = $this->validPackageData();

        $this->actingAs($admin)
            ->post(route('admin.pakets.store'), $data)
            ->assertRedirect();

        $paket = PaketUmroh::where('kode_paket', 'UMR900')->firstOrFail();
        $this->assertDatabaseHas('paket_umroh', [
            'kode_paket' => 'UMR900',
            'nama_paket' => 'Umroh Test',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.pakets.update', $paket), [
                ...$data,
                'nama_paket' => 'Umroh Test Diperbarui',
            ])
            ->assertRedirect(route('admin.pakets.edit', $paket));

        $this->assertDatabaseHas('paket_umroh', [
            'id' => $paket->id,
            'nama_paket' => 'Umroh Test Diperbarui',
        ]);
    }

    public function test_admin_can_change_status_and_deactivate_package(): void
    {
        $admin = $this->createAdmin();
        $paket = PaketUmroh::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.pakets.status', $paket), [
                'status_paket' => PaketUmroh::STATUS_PENUH,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('paket_umroh', [
            'id' => $paket->id,
            'status_paket' => PaketUmroh::STATUS_PENUH,
            'sisa_kuota' => 0,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.pakets.deactivate', $paket))
            ->assertRedirect();

        $this->assertDatabaseHas('paket_umroh', [
            'id' => $paket->id,
            'status_paket' => PaketUmroh::STATUS_DITUTUP,
        ]);
    }

    public function test_package_with_zero_quota_cannot_be_marked_available(): void
    {
        $paket = PaketUmroh::factory()->create([
            'sisa_kuota' => 0,
            'status_paket' => PaketUmroh::STATUS_PENUH,
        ]);

        $this->actingAs($this->createAdmin())
            ->patch(route('admin.pakets.status', $paket), [
                'status_paket' => PaketUmroh::STATUS_TERSEDIA,
            ])
            ->assertSessionHasErrors('status_paket');

        $this->assertDatabaseHas('paket_umroh', [
            'id' => $paket->id,
            'status_paket' => PaketUmroh::STATUS_PENUH,
        ]);
    }

    public function test_backend_validation_rejects_invalid_package_data(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.pakets.store'), [
                ...$this->validPackageData(),
                'kode_paket' => '',
                'harga' => -1,
                'kuota_jamaah' => 10,
                'sisa_kuota' => 11,
            ])
            ->assertSessionHasErrors(['kode_paket', 'harga', 'sisa_kuota']);

        $this->assertDatabaseMissing('paket_umroh', ['nama_paket' => 'Umroh Test']);
    }

    public function test_pelanggan_cannot_manage_packages(): void
    {
        $pelanggan = $this->createPelanggan();
        $paket = PaketUmroh::factory()->create();

        $this->actingAs($pelanggan)
            ->get(route('admin.pakets.index'))
            ->assertForbidden();

        $this->actingAs($pelanggan)
            ->post(route('admin.pakets.store'), $this->validPackageData())
            ->assertForbidden();

        $this->actingAs($pelanggan)
            ->put(route('admin.pakets.update', $paket), $this->validPackageData())
            ->assertForbidden();

        $this->actingAs($pelanggan)
            ->patch(route('admin.pakets.status', $paket), [
                'status_paket' => PaketUmroh::STATUS_DITUTUP,
            ])
            ->assertForbidden();

        $this->actingAs($pelanggan)
            ->patch(route('admin.pakets.deactivate', $paket))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_package_management(): void
    {
        $paket = PaketUmroh::factory()->create();

        $this->get(route('admin.pakets.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.pakets.store'), $this->validPackageData())
            ->assertRedirect(route('login'));

        $this->patch(route('admin.pakets.deactivate', $paket))
            ->assertRedirect(route('login'));
    }

    public function test_admin_package_forms_include_csrf_tokens(): void
    {
        $admin = $this->createAdmin();
        $paket = PaketUmroh::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.pakets.create'))
            ->assertOk()
            ->assertSee('name="_token"', false);

        $this->actingAs($admin)
            ->get(route('admin.pakets.index'))
            ->assertOk()
            ->assertSee('name="_token"', false);

        $this->actingAs($admin)
            ->get(route('admin.pakets.edit', $paket))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    private function validPackageData(): array
    {
        return [
            'kode_paket' => 'UMR900',
            'nama_paket' => 'Umroh Test',
            'jenis_perjalanan' => 'Reguler',
            'durasi' => 9,
            'harga' => 25000000,
            'kota_keberangkatan' => 'Jakarta',
            'tanggal_keberangkatan' => '2026-12-20',
            'kuota_jamaah' => 40,
            'sisa_kuota' => 40,
            'fasilitas' => 'Tiket dan hotel',
            'jadwal_perjalanan' => 'Hari 1: Berangkat',
            'persyaratan_dokumen' => 'Paspor dan KTP',
            'status_paket' => PaketUmroh::STATUS_TERSEDIA,
        ];
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create(['role' => 'admin']);
        Admin::create(['user_id' => $user->id, 'jabatan' => 'Administrator']);

        return $user;
    }

    private function createPelanggan(): User
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        Pelanggan::create(['user_id' => $user->id]);

        return $user;
    }
}
