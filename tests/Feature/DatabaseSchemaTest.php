<?php

namespace Tests\Feature;

use App\Models\PaketUmroh;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_paket_umroh_schema_and_seeder_are_consistent(): void
    {
        $this->seed();

        $this->assertTrue(Schema::hasTable('paket_umroh'));
        $this->assertFalse(Schema::hasTable('paket_tours'));
        $this->assertFalse(Schema::hasColumn('users', 'nik'));
        $this->assertFalse(Schema::hasColumn('users', 'no_ktp'));
        $this->assertFalse(Schema::hasColumn('pelanggans', 'no_ktp'));
        $this->assertFalse(Schema::hasColumn('pelanggans', 'alamat'));
        $this->assertFalse(Schema::hasColumn('pelanggans', 'tanggal_lahir'));
        $this->assertFalse(Schema::hasColumn('pelanggans', 'jenis_kelamin'));
        $this->assertTrue(Schema::hasColumn('pendaftarans', 'user_id'));
        $this->assertTrue(Schema::hasColumn('pendaftarans', 'paket_umroh_id'));
        $this->assertTrue(Schema::hasColumn('pendaftarans', 'tanggal_pendaftaran'));
        $this->assertTrue(Schema::hasColumn('pendaftarans', 'catatan_pelanggan'));
        $this->assertTrue(Schema::hasColumn('keberangkatans', 'paket_umroh_id'));
        $this->assertTrue(Schema::hasColumn('jamaahs', 'nik'));
        $this->assertTrue(Schema::hasColumn('jamaahs', 'no_hp'));
        $this->assertTrue(Schema::hasColumn('jamaahs', 'tanggal_terbit_paspor'));
        $this->assertTrue(Schema::hasColumn('jamaahs', 'tanggal_berakhir_paspor'));
        $this->assertTrue(Schema::hasColumn('dokumens', 'mime_type'));
        $this->assertTrue(Schema::hasColumn('dokumens', 'catatan_admin'));
        $this->assertTrue(Schema::hasColumn('dokumens', 'verified_by'));
        $this->assertTrue(Schema::hasColumn('dokumens', 'verified_at'));
        $this->assertTrue(Schema::hasColumn('pembayarans', 'nominal'));
        $this->assertTrue(Schema::hasColumn('pembayarans', 'bukti_file'));
        $this->assertTrue(Schema::hasColumn('pembayarans', 'verified_by'));
        $this->assertTrue(Schema::hasColumn('pembayarans', 'verified_at'));
        $this->assertTrue(Schema::hasColumn('invoices', 'kode_invoice'));
        $this->assertTrue(Schema::hasColumn('invoices', 'total_dibayar'));
        $this->assertSame('paket_umroh', (new PaketUmroh)->getTable());
        $this->assertDatabaseCount('paket_umroh', 5);
        $this->assertDatabaseHas('paket_umroh', [
            'kode_paket' => 'UMR001',
            'nama_paket' => 'Umroh Hemat Berfaidah',
            'harga' => 24500000,
            'status_paket' => PaketUmroh::STATUS_TERSEDIA,
        ]);
    }
}
