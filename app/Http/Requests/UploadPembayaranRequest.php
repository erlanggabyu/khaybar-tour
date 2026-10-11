<?php

namespace App\Http\Requests;

use App\Models\Pembayaran;
use Illuminate\Foundation\Http\FormRequest;

class UploadPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pembayaran = $this->route('pembayaran');

        return $this->user()?->isPelanggan() === true
            && $pembayaran instanceof Pembayaran
            && $pembayaran->pendaftaran->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'bukti_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }
}
