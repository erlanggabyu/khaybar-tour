<?php

namespace App\Http\Requests;

use App\Models\PaketUmroh;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePaketUmrohRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return $this->paketRules();
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $this->input('status_paket') === PaketUmroh::STATUS_TERSEDIA
                    && (int) $this->input('sisa_kuota') < 1
                ) {
                    $validator->errors()->add(
                        'sisa_kuota',
                        'Paket berstatus tersedia harus memiliki sisa kuota minimal 1.'
                    );
                }
            },
        ];
    }

    protected function paketRules(?int $ignoreId = null): array
    {
        return [
            'kode_paket' => [
                'required',
                'string',
                'max:30',
                Rule::unique('paket_umroh', 'kode_paket')->ignore($ignoreId),
            ],
            'nama_paket' => ['required', 'string', 'max:255'],
            'jenis_perjalanan' => ['required', 'string', 'max:100'],
            'durasi' => ['required', 'integer', 'min:1', 'max:60'],
            'harga' => ['required', 'numeric', 'min:0'],
            'kota_keberangkatan' => ['required', 'string', 'max:100'],
            'tanggal_keberangkatan' => ['required', 'date'],
            'kuota_jamaah' => ['required', 'integer', 'min:1'],
            'sisa_kuota' => ['required', 'integer', 'min:0', 'lte:kuota_jamaah'],
            'fasilitas' => ['nullable', 'string', 'max:5000'],
            'jadwal_perjalanan' => ['nullable', 'string', 'max:10000'],
            'persyaratan_dokumen' => ['nullable', 'string', 'max:5000'],
            'status_paket' => ['required', Rule::in(PaketUmroh::statuses())],
        ];
    }
}
