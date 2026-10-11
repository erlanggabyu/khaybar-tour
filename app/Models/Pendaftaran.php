<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Pendaftaran extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU_DP = 'Menunggu Pembayaran DP';

    public const STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN = 'Menunggu Verifikasi Pembayaran';

    public const STATUS_DP_TERVERIFIKASI = 'DP Terverifikasi';

    public const STATUS_LUNAS = 'Lunas';

    public const STATUS_DIBATALKAN = 'Dibatalkan';

    public static function statuses(): array
    {
        return [
            'Draft',
            self::STATUS_MENUNGGU_DP,
            self::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN,
            self::STATUS_DP_TERVERIFIKASI,
            'Dokumen Belum Lengkap',
            'Dokumen Perlu Revisi',
            'Dokumen Lengkap',
            'Menunggu Pelunasan',
            self::STATUS_LUNAS,
            'Siap Berangkat',
            'Berangkat',
            'Selesai',
            self::STATUS_DIBATALKAN,
        ];
    }

    protected $table = 'pendaftarans';

    protected $fillable = [
        'kode_pendaftaran',
        'user_id',
        'paket_umroh_id',
        'tanggal_pendaftaran',
        'jumlah_jamaah',
        'total_harga',
        'catatan_pelanggan',
        'status_pendaftaran',
        'catatan_admin',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'jumlah_jamaah' => 'integer',
        'tanggal_pendaftaran' => 'date',
    ];

    /**
     * Get the customer profile that made this registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the tour package chosen.
     */
    public function paketUmroh(): BelongsTo
    {
        return $this->belongsTo(PaketUmroh::class, 'paket_umroh_id');
    }

    public function canBeCancelled(): bool
    {
        return $this->status_pendaftaran === self::STATUS_MENUNGGU_DP;
    }

    public function canDeleteJamaah(): bool
    {
        return $this->status_pendaftaran === self::STATUS_MENUNGGU_DP
            && ! $this->pembayarans()
                ->where(function ($query): void {
                    $query->where('status_pembayaran', '!=', Pembayaran::STATUS_BELUM_BAYAR)
                        ->orWhereNotNull('bukti_file');
                })
                ->exists();
    }

    public function canUploadDocuments(): bool
    {
        $hasValidPayment = $this->pembayarans()
            ->where('status_pembayaran', Pembayaran::STATUS_VALID)
            ->exists();

        $hasValidInstallment = $this->cicilans()
            ->where('status', Cicilan::STATUS_VALID)
            ->exists();

        return $hasValidPayment || $hasValidInstallment;
    }

    /**
     * Get the list of physical pilgrims registered under this record.
     */
    public function jamaahs(): HasMany
    {
        return $this->hasMany(Jamaah::class, 'pendaftaran_id');
    }

    /**
     * Get the payment transactions recorded.
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'pendaftaran_id');
    }

    public function cicilans(): HasMany
    {
        return $this->hasMany(Cicilan::class, 'pendaftaran_id');
    }

    public function ensurePaymentBills(): void
    {
        $dp = round((float) $this->total_harga * 0.3, 2);
        $remaining = (float) $this->total_harga - $dp;

        $this->pembayarans()->firstOrCreate(
            ['jenis_pembayaran' => Pembayaran::JENIS_DP],
            ['nominal' => $dp, 'status_pembayaran' => Pembayaran::STATUS_BELUM_BAYAR],
        );
        $this->pembayarans()->firstOrCreate(
            ['jenis_pembayaran' => Pembayaran::JENIS_PELUNASAN],
            ['nominal' => $remaining, 'status_pembayaran' => Pembayaran::STATUS_BELUM_BAYAR],
        );

        $baseInstallment = floor(($remaining / 4) * 100) / 100;
        $allocated = 0;
        for ($number = 1; $number <= 4; $number++) {
            $nominal = $number === 4 ? round($remaining - $allocated, 2) : $baseInstallment;
            $allocated += $nominal;
            $this->cicilans()->firstOrCreate(
                ['cicilan_ke' => $number],
                [
                    'nominal' => $nominal,
                    'due_date' => now()->addMonths($number)->toDateString(),
                    'status' => Cicilan::STATUS_BELUM_BAYAR,
                ],
            );
        }

        $this->syncInvoice();
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'pendaftaran_id');
    }

    public function syncInvoice(): Invoice
    {
        $totalDibayar = (float) $this->pembayarans()
            ->where('status_pembayaran', Pembayaran::STATUS_VALID)
            ->sum('nominal');
        $totalDibayar += (float) $this->cicilans()
            ->where('status', Cicilan::STATUS_VALID)
            ->sum('nominal');
        $status = match (true) {
            $this->status_pendaftaran === self::STATUS_DIBATALKAN => Invoice::STATUS_DIBATALKAN,
            $totalDibayar >= (float) $this->total_harga => Invoice::STATUS_LUNAS,
            default => Invoice::STATUS_BELUM_LUNAS,
        };

        $existingInvoice = $this->invoice()->first();
        $invoice = $this->invoice()->updateOrCreate(
            ['pendaftaran_id' => $this->id],
            [
                'kode_invoice' => $existingInvoice?->kode_invoice ?? $this->generateInvoiceCode(),
                'tanggal_invoice' => now()->toDateString(),
                'total_tagihan' => $this->total_harga,
                'total_dibayar' => $totalDibayar,
                'status_invoice' => $status,
            ],
        );
        $this->setRelation('invoice', $invoice);

        return $invoice;
    }

    private function generateInvoiceCode(): string
    {
        do {
            $code = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Invoice::where('kode_invoice', $code)->exists());

        return $code;
    }

    /**
     * Get the departure schedule.
     */
    public function keberangkatan(): HasOne
    {
        return $this->hasOne(Keberangkatan::class, 'pendaftaran_id');
    }

    /**
     * Get the documents uploaded under this registration.
     */
    public function dokumens(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'pendaftaran_id');
    }
}
