<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Dokumen;
use App\Models\Jamaah;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_pelanggan_can_upload_document_with_random_name_and_metadata(): void
    {
        [$user, $jamaah] = $this->customerAndJamaah();
        $file = UploadedFile::fake()->image('ktp-asli.jpg')->size(500);

        $this->actingAs($user)->post(route('jamaah.dokumen.store', $jamaah), [
            'jenis_dokumen' => 'KTP',
            'file_dokumen' => $file,
        ])->assertRedirect(route('jamaah.dokumen.index', $jamaah));

        $dokumen = Dokumen::firstOrFail();
        $this->assertSame('ktp-asli.jpg', $dokumen->nama_file);
        $this->assertNotSame('dokumen/ktp-asli.jpg', $dokumen->path_file);
        $this->assertSame(Dokumen::STATUS_MENUNGGU, $dokumen->status_dokumen);
        $this->assertNotEmpty($dokumen->mime_type);
        Storage::disk('local')->assertExists($dokumen->path_file);
    }

    public function test_upload_validation_rejects_invalid_type_and_files_larger_than_two_mb(): void
    {
        [$user, $jamaah] = $this->customerAndJamaah();

        $this->actingAs($user)->post(route('jamaah.dokumen.store', $jamaah), [
            'jenis_dokumen' => 'Surat Lain',
            'file_dokumen' => UploadedFile::fake()->create('script.exe', 100, 'application/octet-stream'),
        ])->assertSessionHasErrors(['jenis_dokumen', 'file_dokumen']);

        $this->actingAs($user)->post(route('jamaah.dokumen.store', $jamaah), [
            'jenis_dokumen' => 'Paspor',
            'file_dokumen' => UploadedFile::fake()->create('paspor.pdf', 2049, 'application/pdf'),
        ])->assertSessionHasErrors('file_dokumen');

        $this->assertDatabaseCount('dokumens', 0);
    }

    public function test_pelanggan_cannot_upload_document_before_initial_payment_is_valid(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        Pelanggan::create(['user_id' => $user->id]);
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id]);
        $pendaftaran->ensurePaymentBills();
        $jamaah = Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id]);

        $this->actingAs($user)->get(route('jamaah.dokumen.create', $jamaah))->assertUnprocessable();
        $this->actingAs($user)->post(route('jamaah.dokumen.store', $jamaah), [
            'jenis_dokumen' => 'KTP',
            'file_dokumen' => UploadedFile::fake()->image('ktp.jpg'),
        ])->assertForbidden();

        $this->assertDatabaseCount('dokumens', 0);
    }

    public function test_pelanggan_can_reupload_only_invalid_or_revision_document(): void
    {
        [$user, $jamaah] = $this->customerAndJamaah();
        $dokumen = $this->createDocument($jamaah, Dokumen::STATUS_PERLU_REVISI);
        Storage::disk('local')->put($dokumen->path_file, 'old');
        $oldPath = $dokumen->path_file;

        $this->actingAs($user)->post(route('jamaah.dokumen.store', $jamaah), [
            'jenis_dokumen' => $dokumen->jenis_dokumen,
            'file_dokumen' => UploadedFile::fake()->create('revisi.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('jamaah.dokumen.index', $jamaah));

        $dokumen->refresh();
        $this->assertSame(Dokumen::STATUS_MENUNGGU, $dokumen->status_dokumen);
        $this->assertNull($dokumen->catatan_admin);
        $this->assertNull($dokumen->verified_by);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($dokumen->path_file);

        $dokumen->update(['status_dokumen' => Dokumen::STATUS_VALID]);
        $currentPath = $dokumen->path_file;
        $this->actingAs($user)->post(route('jamaah.dokumen.store', $jamaah), [
            'jenis_dokumen' => $dokumen->jenis_dokumen,
            'file_dokumen' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('jenis_dokumen');
        $this->assertSame($currentPath, $dokumen->fresh()->path_file);
    }

    public function test_pelanggan_can_delete_pending_document_but_not_verified_document(): void
    {
        [$user, $jamaah] = $this->customerAndJamaah();
        $pending = $this->createDocument($jamaah, Dokumen::STATUS_MENUNGGU, 'KTP');
        Storage::disk('local')->put($pending->path_file, 'pending');

        $this->actingAs($user)->delete(route('dokumen.destroy', $pending))
            ->assertRedirect(route('jamaah.dokumen.index', $jamaah));
        $this->assertDatabaseMissing('dokumens', ['id' => $pending->id]);
        Storage::disk('local')->assertMissing($pending->path_file);

        $valid = $this->createDocument($jamaah, Dokumen::STATUS_VALID, 'Paspor');
        $this->actingAs($user)->delete(route('dokumen.destroy', $valid))->assertUnprocessable();
        $this->assertDatabaseHas('dokumens', ['id' => $valid->id]);
    }

    public function test_pelanggan_cannot_access_or_modify_another_customers_documents(): void
    {
        [$owner, $jamaah] = $this->customerAndJamaah();
        [$other] = $this->customerAndJamaah();
        $dokumen = $this->createDocument($jamaah);

        $this->actingAs($other)->get(route('jamaah.dokumen.index', $jamaah))->assertForbidden();
        $this->actingAs($other)->post(route('jamaah.dokumen.store', $jamaah), [
            'jenis_dokumen' => 'Foto',
            'file_dokumen' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertForbidden();
        $this->actingAs($other)->delete(route('dokumen.destroy', $dokumen))->assertForbidden();
    }

    public function test_document_download_is_private_to_owner_and_admin(): void
    {
        [$owner, $jamaah] = $this->customerAndJamaah();
        [$other] = $this->customerAndJamaah();
        $admin = $this->createAdmin();
        $dokumen = $this->createDocument($jamaah);
        Storage::disk('local')->put($dokumen->path_file, 'private-document');

        $this->actingAs($owner)->get(route('dokumen.download', $dokumen))->assertOk();
        $this->actingAs($admin)->get(route('dokumen.download', $dokumen))->assertOk();
        $this->actingAs($other)->get(route('dokumen.download', $dokumen))->assertForbidden();
        $this->post(route('logout'));
        $this->get(route('dokumen.download', $dokumen))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_and_verify_documents_with_required_revision_note(): void
    {
        $admin = $this->createAdmin();
        [, $jamaah] = $this->customerAndJamaah();
        $dokumen = $this->createDocument($jamaah);

        $this->actingAs($admin)->get(route('admin.dokumen.index'))->assertOk()->assertSee($jamaah->nama_lengkap);
        $this->actingAs($admin)->get(route('admin.dokumen.show', $dokumen))->assertOk()->assertSee($dokumen->nama_file);

        $this->actingAs($admin)->post(route('admin.dokumen.verify', $dokumen), [
            'status_dokumen' => Dokumen::STATUS_PERLU_REVISI,
            'catatan_admin' => '',
        ])->assertSessionHasErrors('catatan_admin');

        $this->actingAs($admin)->post(route('admin.dokumen.verify', $dokumen), [
            'status_dokumen' => Dokumen::STATUS_PERLU_REVISI,
            'catatan_admin' => 'Foto dokumen kurang jelas.',
        ])->assertRedirect();

        $dokumen->refresh();
        $this->assertSame(Dokumen::STATUS_PERLU_REVISI, $dokumen->status_dokumen);
        $this->assertSame($admin->id, $dokumen->verified_by);
        $this->assertNotNull($dokumen->verified_at);
    }

    public function test_guest_and_roles_are_restricted_and_forms_include_csrf(): void
    {
        [$user, $jamaah] = $this->customerAndJamaah();
        $admin = $this->createAdmin();
        $dokumen = $this->createDocument($jamaah);

        $this->get(route('jamaah.dokumen.index', $jamaah))->assertRedirect(route('login'));
        $this->get(route('admin.dokumen.index'))->assertRedirect(route('login'));
        $this->actingAs($admin)->get(route('jamaah.dokumen.index', $jamaah))->assertForbidden();
        $this->actingAs($user)->get(route('admin.dokumen.index'))->assertForbidden();

        $this->actingAs($user)->get(route('jamaah.dokumen.create', $jamaah))
            ->assertOk()->assertSee('enctype="multipart/form-data"', false)->assertSee('name="_token"', false);
        $this->actingAs($user)->get(route('jamaah.dokumen.index', $jamaah))
            ->assertOk()->assertSee('name="_method" value="DELETE"', false);
        $this->actingAs($admin)->get(route('admin.dokumen.show', $dokumen))
            ->assertOk()->assertSee('name="_token"', false);
    }

    private function customerAndJamaah(): array
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        Pelanggan::create(['user_id' => $user->id]);
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id]);
        $pendaftaran->ensurePaymentBills();
        $pendaftaran->pembayarans()
            ->where('jenis_pembayaran', Pembayaran::JENIS_DP)
            ->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);
        $jamaah = Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id]);

        return [$user, $jamaah];
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create(['role' => 'admin']);
        Admin::create(['user_id' => $user->id, 'jabatan' => 'Administrator']);

        return $user;
    }

    private function createDocument(Jamaah $jamaah, string $status = Dokumen::STATUS_MENUNGGU, string $type = 'KTP'): Dokumen
    {
        return Dokumen::factory()->create([
            'jamaah_id' => $jamaah->id,
            'pendaftaran_id' => $jamaah->pendaftaran_id,
            'jenis_dokumen' => $type,
            'status_dokumen' => $status,
        ]);
    }
}
