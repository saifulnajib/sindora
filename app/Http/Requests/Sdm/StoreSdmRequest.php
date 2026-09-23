<?php

namespace App\Http\Requests\Sdm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSdmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sdm.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'digits:16'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'tipe' => ['required', Rule::in(['pelatih', 'wasit', 'tenaga'])],
            'cabor_id' => ['nullable', 'integer', Rule::exists('cabors', 'id')],
            'klub_id' => ['nullable', 'integer', Rule::exists('klubs', 'id')],
            'kelurahan_id' => ['nullable', 'integer', Rule::exists('kelurahans', 'id')],
            'lisensi_nomor' => ['nullable', 'string', 'max:100'],
            'nomor_lisensi' => ['nullable', 'string', 'max:100'],
            'lisensi_level' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'string', 'max:50'],
            'kategori_tenaga' => ['nullable', 'string', 'max:100'],
            'lisensi_terbit' => ['nullable', 'date'],
            'tanggal_terbit' => ['nullable', 'date'],
            'expired_at' => ['nullable', 'date', 'after:lisensi_terbit', 'after:tanggal_terbit'],
            'spesialisasi' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'email' => ['nullable', 'email', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus 16 digit.',
            'tipe.in' => 'Tipe harus salah satu dari: pelatih, wasit, tenaga.',
            'expired_at.after' => 'Tanggal kadaluarsa harus setelah tanggal terbit.',
        ];
    }
}
