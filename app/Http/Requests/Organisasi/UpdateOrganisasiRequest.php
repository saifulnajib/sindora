<?php

namespace App\Http\Requests\Organisasi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganisasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('organisasi.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'singkatan' => ['nullable', 'string', 'max:20'],
            'jenis' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'ketua' => ['nullable', 'string', 'max:255'],
            'kontak_hp' => ['nullable', 'string', 'max:30'],
            'kontak_email' => ['nullable', 'email', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
