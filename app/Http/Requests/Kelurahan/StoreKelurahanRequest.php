<?php

namespace App\Http\Requests\Kelurahan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKelurahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('wilayah.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'kecamatan_id' => ['required', 'integer', Rule::exists('kecamatans', 'id')],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:20', 'unique:kelurahans,kode'],
        ];
    }
}
