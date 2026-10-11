<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Cicilan extends Model
{
    use HasFactory;

    public const STATUS_BELUM_BAYAR = 'Belum Bayar';

    public const STATUS_MENUNGGU = 'Menunggu Verifikasi';

    public const STATUS_VALID = 'Valid';

    public const STATUS_TIDAK_VALID = 'Tidak Valid';

    protected $table = 'cicilans';

    protected $fillable = [
        'pendaftaran_id',
        'cicilan_ke',
        'nominal',
        'due_date',
        'status',
        'bukti_file',
        'nama_file',
        'mime_type',
        'ukuran_file',
        'catatan_admin',
        'verified_by',
        'verified_at',
        'paid_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'due_date' => 'date',
        'ukuran_file' => 'integer',
        'verified_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Relation to the registration (pendaftaran).
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Delete stored file when a cicilan record is removed.
     */
    protected static function booted(): void
    {
        static::deleted(function (Cicilan $cicilan): void {
            if ($cicilan->bukti_file) {
                Storage::disk('local')->delete($cicilan->bukti_file);
            }
        });
    }

    /**
     * Determine if the cicilan can be uploaded (still unpaid or invalid).
     */
    public function canBeUploaded(): bool
    {
        return in_array($this->status, [self::STATUS_BELUM_BAYAR, self::STATUS_TIDAK_VALID], true);
    }

    public function isAvailableForCustomer(): bool
    {
        $dpIsValid = $this->pendaftaran->pembayarans()
            ->where('jenis_pembayaran', Pembayaran::JENIS_DP)
            ->where('status_pembayaran', Pembayaran::STATUS_VALID)
            ->exists();

        $directSettlementStarted = $this->pendaftaran->pembayarans()
            ->where('jenis_pembayaran', Pembayaran::JENIS_PELUNASAN)
            ->where(function ($query): void {
                $query->where('status_pembayaran', '!=', Pembayaran::STATUS_BELUM_BAYAR)
                    ->orWhereNotNull('bukti_file');
            })
            ->exists();

        return $dpIsValid && ! $directSettlementStarted;
    }

    public function label(): string
    {
        return 'Cicilan '.$this->cicilan_ke;
    }
}
