<?php

namespace App\Http\Requests;

use App\Models\Pembayaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'status_pembayaran' => ['required', Rule::in([Pembayaran::STATUS_VALID, Pembayaran::STATUS_TIDAK_VALID])],
            'catatan_admin' => [
                Rule::requiredIf($this->input('status_pembayaran') === Pembayaran::STATUS_TIDAK_VALID),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
