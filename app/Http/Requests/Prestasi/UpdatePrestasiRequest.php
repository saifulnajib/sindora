<?php

namespace App\Http\Requests\Prestasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrestasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('prestasi.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'atlet_id' => ['required', 'integer', Rule::exists('atlets', 'id')],
            'cabor_id' => ['required', 'integer', Rule::exists('cabors', 'id')],
            'kejuaraan_id' => ['required', 'integer', Rule::exists('kejuaraans', 'id')],
            'medali' => ['required', Rule::in(['emas', 'perak', 'perunggu', 'juara_harapan'])],
            'peringkat' => ['nullable', 'integer', 'min:1', 'max:999'],
            'tanggal' => ['required', 'date'],
            'kategori_kelas' => ['nullable', 'string', 'max:255'],
            'nomor_sertifikat' => ['nullable', 'string', 'max:255'],
            'sertifikat' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'medali.in' => 'Medali harus salah satu dari: emas, perak, perunggu, juara_harapan.',
        ];
    }
}
