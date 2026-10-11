<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaketUmroh extends Model
{
    use HasFactory;

    public const STATUS_TERSEDIA = 'tersedia';

    public const STATUS_PENUH = 'penuh';

    public const STATUS_DITUTUP = 'ditutup';

    public const STATUS_SELESAI = 'selesai';

    protected $table = 'paket_umroh';

    protected $fillable = [
        'kode_paket',
        'nama_paket',
        'jenis_perjalanan',
        'durasi',
        'harga',
        'kota_keberangkatan',
        'tanggal_keberangkatan',
        'kuota_jamaah',
        'sisa_kuota',
        'fasilitas',
        'jadwal_perjalanan',
        'persyaratan_dokumen',
        'status_paket',
        'program_ibadah',
        'hotel_mekkah',
        'hotel_madinah',
        'maskapai',
        'embarkasi',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_TERSEDIA,
            self::STATUS_PENUH,
            self::STATUS_DITUTUP,
            self::STATUS_SELESAI,
        ];
    }

    public function scopeTersedia(Builder $query): Builder
    {
        return $query
            ->where('status_paket', self::STATUS_TERSEDIA)
            ->where('sisa_kuota', '>', 0);
    }

    protected $casts = [
        'tanggal_keberangkatan' => 'date',
        'harga' => 'decimal:2',
        'kuota_jamaah' => 'integer',
        'sisa_kuota' => 'integer',
        'durasi' => 'integer',
    ];

    /**
     * Get the registrations for this tour package.
     */
    public function pendaftarans(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'paket_umroh_id');
    }

    /**
     * Get the departures scheduled for this package.
     */
    public function keberangkatans(): HasMany
    {
        return $this->hasMany(Keberangkatan::class, 'paket_umroh_id');
    }

    /**
     * Helper to check if quota is available.
     */
    public function isAvailable(): bool
    {
        return $this->status_paket === self::STATUS_TERSEDIA && $this->sisa_kuota > 0;
    }
}
