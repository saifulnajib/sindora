<?php

namespace App\Http\Requests\Kelurahan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKelurahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('wilayah.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('kelurahan') ? (is_object($this->route('kelurahan')) ? $this->route('kelurahan')->id : $this->route('kelurahan')) : null;

        return [
            'kecamatan_id' => ['required', 'integer', Rule::exists('kecamatans', 'id')],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:20', Rule::unique('kelurahans', 'kode')->ignore($id)],
        ];
    }
}
