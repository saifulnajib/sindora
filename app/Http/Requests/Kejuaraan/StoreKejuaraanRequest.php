<?php

namespace App\Http\Requests\Kejuaraan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKejuaraanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        return $user->can('kejuaraan.manage')
            || $user->hasRole(['super_admin', 'operator_organisasi']);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['nullable', 'string', Rule::in(['turnamen', 'liga', 'festival', 'kejuaraan', 'kegiatan'])],
            'tingkat' => ['required', Rule::in(['kabupaten', 'kota', 'kabupaten_kota', 'kecamatan', 'provinsi', 'nasional', 'internasional'])],
            'penyelenggara' => ['required', 'string', 'max:255'],
            'organisasi_id' => ['nullable', 'integer', Rule::exists('organisasis', 'id')],
            'cabor_id' => ['nullable', 'integer', Rule::exists('cabors', 'id')],
            'lokasi' => ['required', 'string', 'max:500'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'poster' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'tingkat.in' => 'Tingkat harus salah satu dari: kabupaten, kota, provinsi, nasional, internasional.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ];
    }
}
