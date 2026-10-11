<?php

namespace App\Http\Requests;

use App\Models\Dokumen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'status_dokumen' => ['required', Rule::in(Dokumen::verificationStatuses())],
            'catatan_admin' => [
                Rule::requiredIf($this->input('status_dokumen') !== Dokumen::STATUS_VALID),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
