<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Jamaah;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JamaahTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelanggan_can_create_view_and_update_jamaah_on_own_registration(): void
    {
        $user = $this->createPelanggan();
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id, 'jumlah_jamaah' => 2]);

        $response = $this->actingAs($user)->post(route('pendaftaran.jamaah.store', $pendaftaran), $this->validData());
        $jamaah = Jamaah::firstOrFail();

        $response->assertRedirect(route('jamaah.show', $jamaah));
        $this->assertDatabaseHas('jamaahs', ['pendaftaran_id' => $pendaftaran->id, 'nik' => '3174011505900001']);
        $this->actingAs($user)->get(route('pendaftaran.jamaah.index', $pendaftaran))->assertOk()->assertSee($jamaah->nama_lengkap);
        $this->actingAs($user)->get(route('jamaah.show', $jamaah))->assertOk()->assertSee($jamaah->nik);

        $this->actingAs($user)->put(route('jamaah.update', $jamaah), [
            ...$this->validData(),
            'nama_lengkap' => 'Jamaah Diperbarui',
        ])->assertRedirect(route('jamaah.show', $jamaah));
        $this->assertDatabaseHas('jamaahs', ['id' => $jamaah->id, 'nama_lengkap' => 'Jamaah Diperbarui']);
    }

    public function test_jamaah_validation_enforces_required_numeric_nik_and_unique_per_registration(): void
    {
        $user = $this->createPelanggan();
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id, 'jumlah_jamaah' => 3]);
        Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id, 'nik' => '3174011505900001']);

        $this->actingAs($user)->post(route('pendaftaran.jamaah.store', $pendaftaran), [
            ...$this->validData(),
            'nama_lengkap' => '',
            'nik' => 'bukan-angka',
            'tanggal_lahir' => '',
            'alamat' => '',
            'no_hp' => '',
        ])->assertSessionHasErrors(['nama_lengkap', 'nik', 'tanggal_lahir', 'alamat', 'no_hp']);

        $this->actingAs($user)->post(route('pendaftaran.jamaah.store', $pendaftaran), $this->validData())
            ->assertSessionHasErrors('nik');

        $other = Pendaftaran::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->post(route('pendaftaran.jamaah.store', $other), $this->validData())
            ->assertRedirect();
    }

    public function test_one_user_can_have_many_jamaah_with_different_nik(): void
    {
        $user = $this->createPelanggan();
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id, 'jumlah_jamaah' => 2]);

        $this->actingAs($user)->post(route('pendaftaran.jamaah.store', $pendaftaran), [
            ...$this->validData(),
            'nik' => '3174011505900001',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('pendaftaran.jamaah.store', $pendaftaran), [
            ...$this->validData(),
            'nama_lengkap' => 'Jamaah Kedua',
            'nik' => '3174011505900002',
            'email' => 'jamaah2@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('jamaahs', ['pendaftaran_id' => $pendaftaran->id, 'nik' => '3174011505900001']);
        $this->assertDatabaseHas('jamaahs', ['pendaftaran_id' => $pendaftaran->id, 'nik' => '3174011505900002']);
    }

    public function test_jamaah_count_cannot_exceed_registration_limit(): void
    {
        $user = $this->createPelanggan();
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id, 'jumlah_jamaah' => 1]);
        Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id]);

        $this->actingAs($user)->get(route('pendaftaran.jamaah.create', $pendaftaran))->assertUnprocessable();
        $this->actingAs($user)->post(route('pendaftaran.jamaah.store', $pendaftaran), $this->validData())->assertUnprocessable();
        $this->assertDatabaseCount('jamaahs', 1);
    }

    public function test_pelanggan_cannot_access_jamaah_from_another_registration(): void
    {
        $owner = $this->createPelanggan();
        $other = $this->createPelanggan();
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $owner->id, 'jumlah_jamaah' => 2]);
        $jamaah = Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id]);

        $this->actingAs($other)->get(route('pendaftaran.jamaah.index', $pendaftaran))->assertForbidden();
        $this->actingAs($other)->get(route('jamaah.show', $jamaah))->assertForbidden();
        $this->actingAs($other)->put(route('jamaah.update', $jamaah), $this->validData())->assertForbidden();
        $this->actingAs($other)->delete(route('jamaah.destroy', $jamaah))->assertForbidden();
    }

    public function test_jamaah_can_only_be_deleted_before_payment_or_verification(): void
    {
        $user = $this->createPelanggan();
        $open = Pendaftaran::factory()->create(['user_id' => $user->id, 'jumlah_jamaah' => 2]);
        $deletable = Jamaah::factory()->create(['pendaftaran_id' => $open->id]);

        $this->actingAs($user)->delete(route('jamaah.destroy', $deletable))
            ->assertRedirect(route('pendaftaran.jamaah.index', $open));
        $this->assertDatabaseMissing('jamaahs', ['id' => $deletable->id]);

        $withPayment = Pendaftaran::factory()->create(['user_id' => $user->id]);
        $locked = Jamaah::factory()->create(['pendaftaran_id' => $withPayment->id]);
        Pembayaran::create([
            'pendaftaran_id' => $withPayment->id,
            'jenis_pembayaran' => 'DP',
            'nominal' => 1000000,
            'status_pembayaran' => 'Menunggu Verifikasi',
        ]);

        $this->actingAs($user)->delete(route('jamaah.destroy', $locked))->assertUnprocessable();
        $this->assertDatabaseHas('jamaahs', ['id' => $locked->id]);
    }

    public function test_admin_can_view_all_jamaah_but_cannot_use_customer_management_routes(): void
    {
        $admin = $this->createAdmin();
        $pendaftaran = Pendaftaran::factory()->create(['jumlah_jamaah' => 2]);
        $jamaah = Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id]);

        $this->actingAs($admin)->get(route('admin.pendaftaran.jamaah.index', $pendaftaran))
            ->assertOk()->assertSee($jamaah->nama_lengkap);
        $this->actingAs($admin)->get(route('admin.jamaah.show', $jamaah))
            ->assertOk()->assertSee($jamaah->nik);
        $this->actingAs($admin)->get(route('jamaah.edit', $jamaah))->assertForbidden();
    }

    public function test_guest_is_redirected_and_jamaah_forms_have_csrf_tokens(): void
    {
        $user = $this->createPelanggan();
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id, 'jumlah_jamaah' => 2]);
        $jamaah = Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id]);

        $this->get(route('pendaftaran.jamaah.index', $pendaftaran))->assertRedirect(route('login'));
        $this->get(route('admin.jamaah.show', $jamaah))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('pendaftaran.jamaah.create', $pendaftaran))
            ->assertOk()->assertSee('name="_token"', false);
        $this->actingAs($user)->get(route('jamaah.edit', $jamaah))
            ->assertOk()->assertSee('name="_token"', false)->assertSee('name="_method" value="PUT"', false);
        $this->actingAs($user)->get(route('jamaah.show', $jamaah))
            ->assertOk()->assertSee('name="_method" value="DELETE"', false);
    }

    private function validData(): array
    {
        return [
            'nama_lengkap' => 'Jamaah Uji',
            'nik' => '3174011505900001',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-05-15',
            'alamat' => 'Jl. Pengujian No. 1',
            'no_hp' => '081234567890',
            'email' => 'jamaah@example.com',
            'no_paspor' => '',
            'tanggal_terbit_paspor' => '',
            'tanggal_berakhir_paspor' => '',
        ];
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
