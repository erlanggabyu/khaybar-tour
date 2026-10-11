<?php

namespace App\Http\Requests;

use App\Models\Cicilan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyCicilanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([Cicilan::STATUS_VALID, Cicilan::STATUS_TIDAK_VALID])],
            'catatan_admin' => [
                Rule::requiredIf($this->input('status') === Cicilan::STATUS_TIDAK_VALID),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
