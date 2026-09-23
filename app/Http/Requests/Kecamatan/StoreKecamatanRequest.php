<?php

namespace App\Http\Requests\Kecamatan;

use Illuminate\Foundation\Http\FormRequest;

class StoreKecamatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('wilayah.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:20', 'unique:kecamatans,kode'],
        ];
    }
}
