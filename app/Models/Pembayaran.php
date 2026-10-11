<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Pembayaran extends Model
{
    use HasFactory;

    public const JENIS_DP = 'DP';

    public const JENIS_PELUNASAN = 'Pelunasan';

    public const STATUS_BELUM_BAYAR = 'Belum Bayar';

    public const STATUS_MENUNGGU = 'Menunggu Verifikasi';

    public const STATUS_VALID = 'Valid';

    public const STATUS_TIDAK_VALID = 'Tidak Valid';

    protected $table = 'pembayarans';

    protected $fillable = [
        'pendaftaran_id',
        'jenis_pembayaran',
        'nominal',
        'bukti_file',
        'nama_file',
        'mime_type',
        'ukuran_file',
        'status_pembayaran',
        'catatan_admin',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'ukuran_file' => 'integer',
        'verified_at' => 'datetime',
    ];

    public static function types(): array
    {
        return [self::JENIS_DP, self::JENIS_PELUNASAN];
    }

    public function canBeUploaded(): bool
    {
        return in_array($this->status_pembayaran, [self::STATUS_BELUM_BAYAR, self::STATUS_TIDAK_VALID], true);
    }

    public function isAvailableForCustomer(): bool
    {
        if ($this->jenis_pembayaran === self::JENIS_DP) {
            return true;
        }

        $dpIsValid = $this->pendaftaran->pembayarans()
            ->where('jenis_pembayaran', self::JENIS_DP)
            ->where('status_pembayaran', self::STATUS_VALID)
            ->exists();

        $installmentStarted = $this->pendaftaran->cicilans()
            ->where(function ($query): void {
                $query->where('status', '!=', Cicilan::STATUS_BELUM_BAYAR)
                    ->orWhereNotNull('bukti_file');
            })
            ->exists();

        return $dpIsValid && ! $installmentStarted;
    }

    /**
     * Get the registration associated with this payment.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    protected static function booted(): void
    {
        static::deleted(function (Pembayaran $pembayaran): void {
            if ($pembayaran->bukti_file) {
                Storage::disk('local')->delete($pembayaran->bukti_file);
            }
        });
    }
}
