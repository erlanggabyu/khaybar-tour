<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\PaketUmroh;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_is_created_after_payment_bills_available_and_contains_registration_data(): void
    {
        [$user, $pendaftaran] = $this->customerAndRegistration(100000000);
        Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id, 'nama_lengkap' => 'Jamaah Invoice']);

        $this->actingAs($user)->get(route('pendaftaran.invoice.show', $pendaftaran))
            ->assertOk()
            ->assertSee($pendaftaran->kode_pendaftaran)
            ->assertSee('Jamaah Invoice')
            ->assertSee('DP')
            ->assertSee('Pelunasan');

        $this->assertDatabaseHas('invoices', [
            'pendaftaran_id' => $pendaftaran->id,
            'total_tagihan' => 100000000,
            'total_dibayar' => 0,
            'status_invoice' => Invoice::STATUS_BELUM_LUNAS,
        ]);
        $this->assertDatabaseCount('pembayarans', 2);
    }

    public function test_invoice_synchronizes_dp_and_full_payment_totals(): void
    {
        [, $pendaftaran] = $this->customerAndRegistration(100000000);
        $dp = $this->bill($pendaftaran, Pembayaran::JENIS_DP);
        $pelunasan = $this->bill($pendaftaran, Pembayaran::JENIS_PELUNASAN);

        $dp->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);
        $invoice = $pendaftaran->syncInvoice();
        $this->assertSame('30000000.00', $invoice->total_dibayar);
        $this->assertSame(Invoice::STATUS_BELUM_LUNAS, $invoice->status_invoice);

        $pelunasan->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);
        $pendaftaran->update(['status_pendaftaran' => Pendaftaran::STATUS_LUNAS]);
        $invoice = $pendaftaran->syncInvoice();
        $this->assertSame('100000000.00', $invoice->total_dibayar);
        $this->assertSame(Invoice::STATUS_LUNAS, $invoice->status_invoice);
    }

    public function test_customer_can_only_view_and_print_own_invoice(): void
    {
        [$owner, $pendaftaran] = $this->customerAndRegistration();
        [$other] = $this->customerAndRegistration();
        $invoice = $pendaftaran->invoice;

        $this->actingAs($owner)->get(route('invoice.print', $invoice))
            ->assertOk()->assertSee('Cetak Invoice');
        $this->actingAs($other)->get(route('pendaftaran.invoice.show', $pendaftaran))->assertForbidden();
        $this->actingAs($other)->get(route('invoice.print', $invoice))->assertForbidden();
        $this->post(route('logout'));
        $this->get(route('invoice.print', $invoice))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_invoice_and_registration_report_with_filters(): void
    {
        $admin = $this->createAdmin();
        [, $included] = $this->customerAndRegistration(25000000, ['tanggal_pendaftaran' => '2026-06-10']);
        [, $excluded] = $this->customerAndRegistration(25000000, ['tanggal_pendaftaran' => '2026-05-10']);
        $included->update(['status_pendaftaran' => Pendaftaran::STATUS_DP_TERVERIFIKASI]);

        $this->actingAs($admin)->get(route('admin.invoice.show', $included->invoice))
            ->assertOk()->assertSee($included->invoice->kode_invoice);
        $this->actingAs($admin)->get(route('admin.laporan.pendaftaran', [
            'tanggal_awal' => '2026-06-01',
            'tanggal_akhir' => '2026-06-30',
            'paket_umroh_id' => $included->paket_umroh_id,
            'status_pendaftaran' => Pendaftaran::STATUS_DP_TERVERIFIKASI,
        ]))->assertOk()->assertSee($included->kode_pendaftaran)->assertDontSee($excluded->kode_pendaftaran);
    }

    public function test_admin_payment_report_filters_status_and_calculates_valid_total(): void
    {
        $admin = $this->createAdmin();
        [, $pendaftaran] = $this->customerAndRegistration(100000000);
        $dp = $this->bill($pendaftaran, Pembayaran::JENIS_DP);
        $dp->update(['status_pembayaran' => Pembayaran::STATUS_VALID]);

        $this->actingAs($admin)->get(route('admin.laporan.pembayaran', [
            'status_pembayaran' => Pembayaran::STATUS_VALID,
            'paket_umroh_id' => $pendaftaran->paket_umroh_id,
        ]))->assertOk()
            ->assertSee('30.000.000')
            ->assertDontSee('70.000.000');
    }

    public function test_admin_jamaah_report_sums_registered_people_per_package(): void
    {
        $admin = $this->createAdmin();
        $paket = PaketUmroh::factory()->create(['nama_paket' => 'Paket Rekap']);
        [, $first] = $this->customerAndRegistration(25000000, ['paket_umroh_id' => $paket->id, 'jumlah_jamaah' => 2]);
        [, $second] = $this->customerAndRegistration(25000000, ['paket_umroh_id' => $paket->id, 'jumlah_jamaah' => 3]);

        $this->actingAs($admin)->get(route('admin.laporan.jamaah', ['paket_umroh_id' => $paket->id]))
            ->assertOk()->assertSee('Paket Rekap')->assertSee('5');

        $this->assertNotSame($first->id, $second->id);
    }

    public function test_reports_require_admin_and_validate_filters(): void
    {
        [$customer] = $this->customerAndRegistration();
        $admin = $this->createAdmin();

        $this->get(route('admin.laporan.pendaftaran'))->assertRedirect(route('login'));
        $this->actingAs($customer)->get(route('admin.laporan.pendaftaran'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.laporan.pendaftaran', [
            'tanggal_awal' => '2026-06-20',
            'tanggal_akhir' => '2026-06-01',
        ]))->assertSessionHasErrors('tanggal_akhir');
        $this->actingAs($admin)->get(route('admin.laporan.pembayaran', [
            'status_pembayaran' => 'Status Palsu',
        ]))->assertSessionHasErrors('status_pembayaran');
        $this->actingAs($admin)->get(route('admin.laporan.pendaftaran', [
            'status_pendaftaran' => 'Status Palsu',
        ]))->assertSessionHasErrors('status_pendaftaran');
    }

    private function customerAndRegistration(float $total = 25000000, array $attributes = []): array
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        Pelanggan::create(['user_id' => $user->id]);
        $pendaftaran = Pendaftaran::factory()->create([
            'user_id' => $user->id,
            'total_harga' => $total,
            ...$attributes,
        ]);
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
