<?php

namespace App\Http\Requests;

use App\Models\Cicilan;
use Illuminate\Foundation\Http\FormRequest;

class UploadCicilanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cicilan = $this->route('cicilan');

        return $this->user()?->isPelanggan() === true
            && $cicilan instanceof Cicilan
            && $cicilan->pendaftaran->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'bukti_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }
}
