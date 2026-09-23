<?php

namespace App\Http\Requests\Kecamatan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKecamatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('wilayah.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('kecamatan') ? (is_object($this->route('kecamatan')) ? $this->route('kecamatan')->id : $this->route('kecamatan')) : null;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:20', Rule::unique('kecamatans', 'kode')->ignore($id)],
        ];
    }
}
