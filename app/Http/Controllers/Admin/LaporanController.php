<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportFilterRequest;
use App\Models\Cicilan;
use App\Models\PaketUmroh;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function pendaftaran(ReportFilterRequest $request): View
    {
        $filters = $request->validated();
        $pendaftarans = $this->applyRegistrationFilters(Pendaftaran::query(), $filters)
            ->with(['user', 'paketUmroh', 'invoice'])
            ->orderByDesc('tanggal_pendaftaran')
            ->get();

        return view('admin.laporan.pendaftaran', $this->viewData($filters, compact('pendaftarans')));
    }

    public function pembayaran(ReportFilterRequest $request): View
    {
        $filters = $request->validated();
        $pembayarans = Pembayaran::with(['pendaftaran.user', 'pendaftaran.paketUmroh', 'verifier'])
            ->when($filters['tanggal_awal'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['tanggal_akhir'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['paket_umroh_id'] ?? null, fn (Builder $query, int $id) => $query->whereHas('pendaftaran', fn (Builder $registration) => $registration->where('paket_umroh_id', $id)))
            ->when($filters['status_pembayaran'] ?? null, fn (Builder $query, string $status) => $query->where('status_pembayaran', $status))
            ->latest()
            ->get();

        $cicilans = Cicilan::with(['pendaftaran.user', 'pendaftaran.paketUmroh', 'verifier'])
            ->when($filters['tanggal_awal'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['tanggal_akhir'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['paket_umroh_id'] ?? null, fn (Builder $query, int $id) => $query->whereHas('pendaftaran', fn (Builder $registration) => $registration->where('paket_umroh_id', $id)))
            ->when($filters['status_pembayaran'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->get()
            ->map(function (Cicilan $cicilan): Cicilan {
                $cicilan->jenis_pembayaran = $cicilan->label();
                $cicilan->status_pembayaran = $cicilan->status;

                return $cicilan;
            });

        $pembayarans = $pembayarans->merge($cicilans)->sortByDesc('created_at')->values();

        return view('admin.laporan.pembayaran', $this->viewData($filters, compact('pembayarans')));
    }

    public function jamaah(ReportFilterRequest $request): View
    {
        $filters = $request->validated();
        $pakets = PaketUmroh::query()
            ->withCount(['pendaftarans as jumlah_pendaftaran' => fn (Builder $query) => $this->applyRegistrationFilters($query, $filters)])
            ->withSum(['pendaftarans as jumlah_jamaah_terdaftar' => fn (Builder $query) => $this->applyRegistrationFilters($query, $filters)], 'jumlah_jamaah')
            ->when($filters['paket_umroh_id'] ?? null, fn (Builder $query, int $id) => $query->whereKey($id))
            ->orderBy('kode_paket')
            ->get();

        return view('admin.laporan.jamaah', $this->viewData($filters, compact('pakets')));
    }

    private function applyRegistrationFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['tanggal_awal'] ?? null, fn (Builder $builder, string $date) => $builder->whereDate('tanggal_pendaftaran', '>=', $date))
            ->when($filters['tanggal_akhir'] ?? null, fn (Builder $builder, string $date) => $builder->whereDate('tanggal_pendaftaran', '<=', $date))
            ->when($filters['paket_umroh_id'] ?? null, fn (Builder $builder, int $id) => $builder->where('paket_umroh_id', $id))
            ->when($filters['status_pendaftaran'] ?? null, fn (Builder $builder, string $status) => $builder->where('status_pendaftaran', $status));
    }

    private function viewData(array $filters, array $data): array
    {
        return [
            ...$data,
            'filters' => $filters,
            'paketOptions' => PaketUmroh::orderBy('kode_paket')->get(),
            'registrationStatuses' => Pendaftaran::query()->distinct()->orderBy('status_pendaftaran')->pluck('status_pendaftaran'),
        ];
    }
}
