<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Dokumen extends Model
{
    use HasFactory;

    public const STATUS_BELUM_UPLOAD = 'Belum Upload';

    public const STATUS_MENUNGGU = 'Menunggu Pemeriksaan';

    public const STATUS_VALID = 'Valid';

    public const STATUS_TIDAK_VALID = 'Tidak Valid';

    public const STATUS_PERLU_REVISI = 'Perlu Revisi';

    protected $table = 'dokumens';

    protected $fillable = [
        'jamaah_id',
        'pendaftaran_id',
        'jenis_dokumen',
        'nama_file',
        'path_file',
        'mime_type',
        'ukuran_file',
        'status_dokumen',
        'catatan_admin',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'ukuran_file' => 'integer',
        'verified_at' => 'datetime',
    ];

    public static function documentTypes(): array
    {
        return ['KTP', 'Kartu Keluarga', 'Paspor', 'Foto', 'Buku Vaksin/Dokumen Kesehatan'];
    }

    public static function verificationStatuses(): array
    {
        return [self::STATUS_VALID, self::STATUS_TIDAK_VALID, self::STATUS_PERLU_REVISI];
    }

    public function canBeReuploaded(): bool
    {
        return in_array($this->status_dokumen, [self::STATUS_TIDAK_VALID, self::STATUS_PERLU_REVISI], true);
    }

    public function canBeDeletedByCustomer(): bool
    {
        return in_array($this->status_dokumen, [self::STATUS_BELUM_UPLOAD, self::STATUS_MENUNGGU], true);
    }

    /**
     * Get the pilgrim that owns the document.
     */
    public function jamaah(): BelongsTo
    {
        return $this->belongsTo(Jamaah::class, 'jamaah_id');
    }

    /**
     * Get the registration associated with the document.
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
        static::deleted(function (Dokumen $dokumen): void {
            Storage::disk('local')->delete($dokumen->path_file);
        });
    }
}
