<?php

namespace App\Http\Requests\Pembinaan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePembinaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pembinaan.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_program' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'target' => ['nullable', 'string'],
            'anggaran' => ['nullable', 'numeric', 'min:0'],
            'sumber_anggaran' => ['nullable', 'string', Rule::in(['APBD', 'APBN', 'Hibah', 'Sponsor'])],
            'tahun_anggaran' => ['nullable', 'integer', 'min:2000', 'max:2030'],
            'periode_mulai' => ['nullable', 'date'],
            'periode_selesai' => ['nullable', 'date', 'after_or_equal:periode_mulai'],
            'evaluasi' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'aktif', 'selesai', 'ditunda'])],
            'organisasi_id' => ['nullable', 'integer', Rule::exists('organisasis', 'id')],
            'cabor_id' => ['nullable', 'integer', Rule::exists('cabors', 'id')],
            'peserta_atlet_ids' => ['nullable', 'array'],
            'peserta_atlet_ids.*' => ['integer', Rule::exists('atlets', 'id')],
            'peserta_klub_ids' => ['nullable', 'array'],
            'peserta_klub_ids.*' => ['integer', Rule::exists('klubs', 'id')],
            'peserta_sdm_ids' => ['nullable', 'array'],
            'peserta_sdm_ids.*' => ['integer', Rule::exists('sdms', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'periode_selesai.after_or_equal' => 'Periode selesai harus setelah atau sama dengan periode mulai.',
            'sumber_anggaran.in' => 'Sumber anggaran harus salah satu dari: APBD, APBN, Hibah, Sponsor.',
            'status.in' => 'Status harus salah satu dari: draft, aktif, selesai, ditunda.',
        ];
    }
}
