<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPelanggan() === true;
    }

    public function rules(): array
    {
        return [
            'jumlah_jamaah' => ['required', 'integer', 'min:1'],
            'catatan_pelanggan' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
