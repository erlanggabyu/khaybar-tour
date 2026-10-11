<?php

namespace App\Http\Requests;

use App\Models\Jamaah;
use App\Models\Pendaftaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJamaahRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pendaftaran = $this->registration();

        return $this->user()?->isPelanggan() === true
            && $pendaftaran?->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        $pendaftaran = $this->registration();
        $jamaah = $this->route('jamaah');

        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'digits:16',
                Rule::unique('jamaahs', 'nik')
                    ->where('pendaftaran_id', $pendaftaran?->id)
                    ->ignore($jamaah?->id),
            ],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'alamat' => ['required', 'string', 'max:2000'],
            'no_hp' => ['required', 'regex:/^[0-9]{8,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_paspor' => ['nullable', 'string', 'max:50'],
            'tanggal_terbit_paspor' => ['nullable', 'date'],
            'tanggal_berakhir_paspor' => ['nullable', 'date', 'after_or_equal:tanggal_terbit_paspor'],
        ];
    }

    protected function registration(): ?Pendaftaran
    {
        $pendaftaran = $this->route('pendaftaran');

        if ($pendaftaran instanceof Pendaftaran) {
            return $pendaftaran;
        }

        $jamaah = $this->route('jamaah');

        return $jamaah instanceof Jamaah ? $jamaah->pendaftaran : null;
    }
}
