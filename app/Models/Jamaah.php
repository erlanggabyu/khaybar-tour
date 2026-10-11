<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jamaah extends Model
{
    use HasFactory;

    protected $table = 'jamaahs';

    protected $fillable = [
        'pendaftaran_id',
        'nama_lengkap',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'email',
        'no_paspor',
        'tanggal_terbit_paspor',
        'tanggal_berakhir_paspor',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_terbit_paspor' => 'date',
        'tanggal_berakhir_paspor' => 'date',
    ];

    /**
     * Get the registration details for this pilgrim.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    /**
     * Get the documents uploaded for this pilgrim.
     */
    public function dokumens(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'jamaah_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (Jamaah $jamaah): void {
            $jamaah->dokumens()->get()->each->delete();
        });
    }
}
