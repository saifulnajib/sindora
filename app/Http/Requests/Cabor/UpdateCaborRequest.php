<?php

namespace App\Http\Requests\Cabor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaborRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cabor.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('cabor') ? (is_object($this->route('cabor')) ? $this->route('cabor')->id : $this->route('cabor')) : null;

        return [
            'organisasi_id' => ['required', 'integer', Rule::exists('organisasis', 'id')],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:20', Rule::unique('cabors', 'kode')->ignore($id)],
            'kategori' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
