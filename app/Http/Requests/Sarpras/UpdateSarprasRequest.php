<?php

namespace App\Http\Requests\Sarpras;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSarprasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sarpras.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'kelurahan_id' => ['nullable', 'integer', Rule::exists('kelurahans', 'id')],
            'kecamatan_id' => ['nullable', 'integer', Rule::exists('kecamatans', 'id')],
            'klub_id' => ['nullable', 'integer', Rule::exists('klubs', 'id')],
            'cabor_id' => ['nullable', 'integer', Rule::exists('cabors', 'id')],
            'latitude' => ['nullable', 'numeric', 'between:0.85,1.05'],
            'longitude' => ['nullable', 'numeric', 'between:104.30,104.55'],
            'kondisi' => ['nullable', Rule::in(['baik', 'rusak_ringan', 'rusak_berat'])],
            'kapasitas' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'fasilitas' => ['nullable', 'array'],
            'fasilitas.*' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.between' => 'Latitude harus dalam bbox Tanjungpinang (0.85 s/d 1.05).',
            'longitude.between' => 'Longitude harus dalam bbox Tanjungpinang (104.30 s/d 104.55).',
            'kondisi.in' => 'Kondisi harus salah satu dari: baik, rusak_ringan, rusak_berat.',
        ];
    }
}
