<?php

namespace App\Http\Requests;

use App\Models\PaketUmroh;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'paket_umroh_id' => ['nullable', 'integer', Rule::exists(PaketUmroh::class, 'id')],
            'status_pendaftaran' => ['nullable', Rule::in(Pendaftaran::statuses())],
            'status_pembayaran' => ['nullable', Rule::in([
                Pembayaran::STATUS_BELUM_BAYAR,
                Pembayaran::STATUS_MENUNGGU,
                Pembayaran::STATUS_VALID,
                Pembayaran::STATUS_TIDAK_VALID,
            ])],
        ];
    }
}
