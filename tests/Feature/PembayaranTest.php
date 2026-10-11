<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Cicilan;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_registration_has_dp_and_settlement_bills_with_correct_nominal(): void
    {
        [$user, $pendaftaran] = $this->customerAndRegistration(100000000);

        $this->actingAs($user)->get(route('pendaftaran.pembayaran.index', $pendaftaran))
            ->assertOk()->assertSee('30%');

        $this->assertDatabaseHas('pembayarans', [
            'pendaftaran_id' => $pendaftaran->id,
            'jenis_pembayaran' => Pembayaran::JENIS_DP,
            'nominal' => 30000000,
        ]);
        $this->assertDatabaseHas('pembayarans', [
            'pendaftaran_id' => $pendaftaran->id,
            'jenis_pembayaran' => Pembayaran::JENIS_PELUNASAN,
            'nominal' => 70000000,
        ]);
        $this->assertDatabaseCount('cicilans', 4);
        $this->assertSame(70000000.0, (float) $pendaftaran->cicilans()->sum('nominal'));
    }

    public function test_customer_can_upload_dp_with_random_private_file_and_metadata(): void
    {
        [$user, $pendaftaran] = $this->customerAndRegistration();
        $dp = $this->bill($pendaftaran, Pembayaran::JENIS_DP);

        $this->actingAs($user)->post(route('pembayaran.store', $dp), [
            'bukti_file' => UploadedFile::fake()->image('transfer-dp.jpg')->size(500),
        ])->assertRedirect(route('pendaftaran.pembayaran.index', $pendaftaran));

        $dp->refresh();
        $this->assertSame(Pembayaran::STATUS_MENUNGGU, $dp->status_pembayaran);
        $this->assertSame('transfer-dp.jpg', $dp->nama_file);
        $this->assertNotSame('pembayaran/transfer-dp.jpg', $dp->bukti_file);
        $this->assertNotEmpty($dp->mime_type);
        $this->assertSame(Pendaftaran::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN, $pendaftaran->fresh()->status_pendaftaran);
        Storage::disk('local')->assertExists($dp->bukti_file);
    }

    public function test_upload_validation_and_ownership_are_enforced(): void
    {
        [$owner, $pendaftaran] = $this->customerAndRegistration();
        [$other] = $this->customerAndRegistration();
        $dp = $this->bill($pendaftaran, Pembayaran::JENIS_DP);

        $this->actingAs($owner)->post(route('pembayaran.store', $dp), [
            'bukti_file' => UploadedFile::fake()->create('script.exe', 100, 'application/octet-stream'),
        ])->assertSessionHasErrors('bukti_file');
        $this->actingAs($owner)->post(route('pembayaran.store', $dp), [
            'bukti_file' => UploadedFile::fake()->create('besar.pdf', 2049, 'application/pdf'),
        ])->assertSessionHasErrors('bukti_file');
        $this->actingAs($other)->get(route('pembayaran.upload', $dp))->assertForbidden();
        $this->actingAs($other)->post(route('pembayaran.store', $dp), [
            'bukti_file' => UploadedFile::fake()->image('asing.jpg'),
        ])->assertForbidden();
    }

    public function test_settlement_upload_is_locked_until_dp_is_valid(): void
    {
        [$user, $pendaftaran] = $this->customerAndRegistration();
        $pelunasan = $this->bill($pendaftaran, Pembayaran::JENIS_PELUNASAN);

        $this->actingAs($user)->get(route('pembayaran.upload', $pelunasan))->assertUnprocessable();
        $this->actingAs($user)->post(route('pembayaran.store', $pelunasan), [
            'bukti_file' => UploadedFile::fake()->image('pelunasan.jpg'),
        ])->assertUnprocessable();

        $this->bill($pendaftaran, Pembayaran::JENIS_DP)->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);
        $this->actingAs($user)->get(route('pembayaran.upload', $pelunasan))->assertOk();
    }

    public function test_installment_upload_is_locked_until_dp_is_valid_and_blocks_direct_settlement(): void
    {
        [$user, $pendaftaran] = $this->customerAndRegistration(100000000);
        $cicilan = $pendaftaran->cicilans()->where('cicilan_ke', 1)->firstOrFail();
        $pelunasan = $this->bill($pendaftaran, Pembayaran::JENIS_PELUNASAN);

        $this->actingAs($user)->get(route('cicilan.upload', $cicilan))->assertUnprocessable();

        $this->bill($pendaftaran, Pembayaran::JENIS_DP)->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);
        $this->actingAs($user)->post(route('cicilan.store', $cicilan), [
            'bukti_file' => UploadedFile::fake()->image('cicilan-1.jpg')->size(500),
        ])->assertRedirect(route('pendaftaran.pembayaran.index', $pendaftaran));

        $cicilan->refresh();
        $this->assertSame(Cicilan::STATUS_MENUNGGU, $cicilan->status);
        Storage::disk('local')->assertExists($cicilan->bukti_file);
        $this->actingAs($user)->get(route('pembayaran.upload', $pelunasan))->assertUnprocessable();
    }

    public function test_customer_can_reupload_only_invalid_payment_and_old_file_is_removed(): void
    {
        [$user, $pendaftaran] = $this->customerAndRegistration();
        $dp = $this->bill($pendaftaran, Pembayaran::JENIS_DP);
        $dp->update([
            'status_pembayaran' => Pembayaran::STATUS_TIDAK_VALID,
            'bukti_file' => 'pembayaran/lama.pdf',
            'nama_file' => 'lama.pdf',
            'catatan_admin' => 'Nominal tidak terlihat.',
        ]);
        Storage::disk('local')->put($dp->bukti_file, 'old');

        $this->actingAs($user)->post(route('pembayaran.store', $dp), [
            'bukti_file' => UploadedFile::fake()->create('revisi.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $dp->refresh();
        Storage::disk('local')->assertMissing('pembayaran/lama.pdf');
        Storage::disk('local')->assertExists($dp->bukti_file);
        $this->assertSame(Pembayaran::STATUS_MENUNGGU, $dp->status_pembayaran);
        $this->assertNull($dp->catatan_admin);

        $dp->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);
        $this->actingAs($user)->post(route('pembayaran.store', $dp), [
            'bukti_file' => UploadedFile::fake()->image('tidak-boleh.jpg'),
        ])->assertUnprocessable();
    }

    public function test_admin_verification_updates_payment_and_registration_status(): void
    {
        $admin = $this->createAdmin();
        [, $pendaftaran] = $this->customerAndRegistration();
        $dp = $this->bill($pendaftaran, Pembayaran::JENIS_DP);
        $dp->update(['status_pembayaran' => Pembayaran::STATUS_MENUNGGU, 'bukti_file' => 'pembayaran/dp.pdf']);

        $this->actingAs($admin)->post(route('admin.pembayaran.verify', $dp), [
            'status_pembayaran' => Pembayaran::STATUS_VALID,
            'catatan_admin' => '',
        ])->assertRedirect();

        $this->assertSame(Pembayaran::STATUS_VALID, $dp->fresh()->status_pembayaran);
        $this->assertSame(Pendaftaran::STATUS_DP_TERVERIFIKASI, $pendaftaran->fresh()->status_pendaftaran);
        $this->assertSame($admin->id, $dp->fresh()->verified_by);
        $this->assertNotNull($dp->fresh()->verified_at);

        $pelunasan = $this->bill($pendaftaran, Pembayaran::JENIS_PELUNASAN);
        $pelunasan->update(['status_pembayaran' => Pembayaran::STATUS_MENUNGGU, 'bukti_file' => 'pembayaran/lunas.pdf']);
        $this->actingAs($admin)->post(route('admin.pembayaran.verify', $pelunasan), [
            'status_pembayaran' => Pembayaran::STATUS_VALID,
        ])->assertRedirect();
        $this->assertSame(Pendaftaran::STATUS_LUNAS, $pendaftaran->fresh()->status_pendaftaran);
    }

    public function test_admin_can_verify_four_installments_until_registration_is_paid_off(): void
    {
        $admin = $this->createAdmin();
        [, $pendaftaran] = $this->customerAndRegistration(100000000);
        $this->bill($pendaftaran, Pembayaran::JENIS_DP)->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);

        foreach ($pendaftaran->cicilans as $cicilan) {
            $cicilan->update([
                'status' => Cicilan::STATUS_MENUNGGU,
                'bukti_file' => 'cicilan/'.$cicilan->cicilan_ke.'.jpg',
            ]);
            $this->actingAs($admin)->post(route('admin.cicilan.verify', $cicilan), [
                'status' => Cicilan::STATUS_VALID,
            ])->assertRedirect();
        }

        $this->assertSame(Pendaftaran::STATUS_LUNAS, $pendaftaran->fresh()->status_pendaftaran);
        $this->assertSame('100000000.00', $pendaftaran->fresh()->syncInvoice()->total_dibayar);
    }

    public function test_invalid_verification_requires_note_and_allows_reupload(): void
    {
        $admin = $this->createAdmin();
        [, $pendaftaran] = $this->customerAndRegistration();
        $dp = $this->bill($pendaftaran, Pembayaran::JENIS_DP);
        $dp->update(['status_pembayaran' => Pembayaran::STATUS_MENUNGGU, 'bukti_file' => 'pembayaran/dp.pdf']);

        $this->actingAs($admin)->post(route('admin.pembayaran.verify', $dp), [
            'status_pembayaran' => Pembayaran::STATUS_TIDAK_VALID,
            'catatan_admin' => '',
        ])->assertSessionHasErrors('catatan_admin');

        $this->actingAs($admin)->post(route('admin.pembayaran.verify', $dp), [
            'status_pembayaran' => Pembayaran::STATUS_TIDAK_VALID,
            'catatan_admin' => 'Bukti transfer tidak terbaca.',
        ])->assertRedirect();
        $this->assertSame(Pembayaran::STATUS_TIDAK_VALID, $dp->fresh()->status_pembayaran);
        $this->assertSame(Pendaftaran::STATUS_MENUNGGU_DP, $pendaftaran->fresh()->status_pendaftaran);
    }

    public function test_admin_cannot_validate_settlement_before_dp_is_valid(): void
    {
        $admin = $this->createAdmin();
        [, $pendaftaran] = $this->customerAndRegistration();
        $pelunasan = $this->bill($pendaftaran, Pembayaran::JENIS_PELUNASAN);
        $pelunasan->update(['status_pembayaran' => Pembayaran::STATUS_MENUNGGU, 'bukti_file' => 'pembayaran/lunas.pdf']);

        $this->actingAs($admin)->post(route('admin.pembayaran.verify', $pelunasan), [
            'status_pembayaran' => Pembayaran::STATUS_VALID,
        ])->assertUnprocessable();

        $this->assertSame(Pembayaran::STATUS_MENUNGGU, $pelunasan->fresh()->status_pembayaran);
        $this->assertNotSame(Pendaftaran::STATUS_LUNAS, $pendaftaran->fresh()->status_pendaftaran);
    }

    public function test_private_download_admin_access_roles_and_csrf(): void
    {
        [$owner, $pendaftaran] = $this->customerAndRegistration();
        [$other] = $this->customerAndRegistration();
        $admin = $this->createAdmin();
        $dp = $this->bill($pendaftaran, Pembayaran::JENIS_DP);
        $dp->update(['bukti_file' => 'pembayaran/dp.pdf', 'nama_file' => 'dp.pdf']);
        Storage::disk('local')->put($dp->bukti_file, 'payment-proof');

        $this->actingAs($owner)->get(route('pembayaran.download', $dp))->assertOk();
        $this->actingAs($admin)->get(route('pembayaran.download', $dp))->assertOk();
        $this->actingAs($other)->get(route('pembayaran.download', $dp))->assertForbidden();
        $this->actingAs($owner)->get(route('admin.pembayaran.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('pendaftaran.pembayaran.index', $pendaftaran))->assertForbidden();
        $this->actingAs($owner)->get(route('pembayaran.upload', $dp))
            ->assertOk()->assertSee('enctype="multipart/form-data"', false)->assertSee('name="_token"', false);

        $this->bill($pendaftaran, Pembayaran::JENIS_DP)->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);
        $cicilan = $pendaftaran->cicilans()->firstOrFail();
        $this->actingAs($owner)->get(route('cicilan.upload', $cicilan))
            ->assertOk()->assertSee('enctype="multipart/form-data"', false)->assertSee('name="_token"', false);
    }

    private function customerAndRegistration(float $total = 25000000): array
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        Pelanggan::create(['user_id' => $user->id]);
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id, 'total_harga' => $total]);
        $pendaftaran->ensurePaymentBills();

        return [$user, $pendaftaran];
    }

    private function bill(Pendaftaran $pendaftaran, string $type): Pembayaran
    {
        return $pendaftaran->pembayarans()->where('jenis_pembayaran', $type)->firstOrFail();
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create(['role' => 'admin']);
        Admin::create(['user_id' => $user->id, 'jabatan' => 'Administrator']);

        return $user;
    }
}
