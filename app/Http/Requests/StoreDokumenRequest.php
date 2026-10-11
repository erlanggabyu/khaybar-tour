<?php

namespace App\Http\Requests;

use App\Models\Dokumen;
use App\Models\Jamaah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        $jamaah = $this->route('jamaah');

        return $this->user()?->isPelanggan() === true
            && $jamaah instanceof Jamaah
            && $jamaah->pendaftaran->user_id === $this->user()->id
            && $jamaah->pendaftaran->canUploadDocuments();
    }

    public function rules(): array
    {
        return [
            'jenis_dokumen' => ['required', Rule::in(Dokumen::documentTypes())],
            'file_dokumen' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }
}
