<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    public const STATUS_BELUM_LUNAS = 'Belum Lunas';

    public const STATUS_LUNAS = 'Lunas';

    public const STATUS_DIBATALKAN = 'Dibatalkan';

    protected $table = 'invoices';

    protected $fillable = [
        'pendaftaran_id',
        'kode_invoice',
        'tanggal_invoice',
        'total_tagihan',
        'total_dibayar',
        'status_invoice',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'total_tagihan' => 'decimal:2',
        'total_dibayar' => 'decimal:2',
    ];

    /**
     * Get the registration details for this invoice.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }
}
