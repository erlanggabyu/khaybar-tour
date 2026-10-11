<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keberangkatan extends Model
{
    use HasFactory;

    protected $table = 'keberangkatans';

    protected $fillable = [
        'pendaftaran_id',
        'paket_umroh_id',
        'tanggal_berangkat',
        'tanggal_kembali',
        'status_keberangkatan',
        'catatan_keberangkatan',
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'tanggal_kembali' => 'date',
    ];

    /**
     * Get the registration details for this departure.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    /**
     * Get the tour package associated.
     */
    public function paketUmroh(): BelongsTo
    {
        return $this->belongsTo(PaketUmroh::class, 'paket_umroh_id');
    }
}
