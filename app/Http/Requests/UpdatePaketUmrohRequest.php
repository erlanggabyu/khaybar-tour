<?php

namespace App\Http\Requests;

class UpdatePaketUmrohRequest extends StorePaketUmrohRequest
{
    public function rules(): array
    {
        return $this->paketRules($this->route('paketUmroh')->id);
    }
}
