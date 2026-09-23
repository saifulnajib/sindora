<?php

namespace App\Http\Requests\Klub;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKlubRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('klub.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'cabor_id' => ['required', 'integer', Rule::exists('cabors', 'id')],
            'kelurahan_id' => ['nullable', 'integer', Rule::exists('kelurahans', 'id')],
            'kecamatan_id' => ['nullable', 'integer', Rule::exists('kecamatans', 'id')],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'ketua' => ['nullable', 'string', 'max:255'],
            'kontak_hp' => ['nullable', 'string', 'max:30'],
            'kontak_email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'dokumen_legalitas' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'jadwal_latihan' => ['nullable', 'array'],
            'jadwal_latihan.*.hari' => ['nullable', 'string', 'max:20'],
            'jadwal_latihan.*.jam_mulai' => ['nullable', 'string', 'max:10'],
            'jadwal_latihan.*.jam_selesai' => ['nullable', 'string', 'max:10'],
            'jadwal_latihan.*.lokasi' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:0.85,1.05'],
            'longitude' => ['nullable', 'numeric', 'between:104.30,104.55'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'nomor_sk' => ['nullable', 'string', 'max:100'],
            'tanggal_sk' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.between' => 'Latitude harus dalam bbox Tanjungpinang (0.85 s/d 1.05).',
            'longitude.between' => 'Longitude harus dalam bbox Tanjungpinang (104.30 s/d 104.55).',
        ];
    }
}
