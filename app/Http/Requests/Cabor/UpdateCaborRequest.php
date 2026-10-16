<?php

namespace App\Http\Requests\Cabor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaborRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cabor = $this->route('cabor');
        if ($cabor instanceof \App\Models\Cabor) {
            return $this->user() ? $this->user()->can('update', $cabor) : false;
        }

        return $this->user()?->can('cabor.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('cabor') ? (is_object($this->route('cabor')) ? $this->route('cabor')->id : $this->route('cabor')) : null;
        $isSuperAdmin = $this->user()?->hasRole('super_admin');

        return [
            // Core cabor data is only required for Super Admin; for Pengkot it is locked/ignored
            'organisasi_id' => [$isSuperAdmin ? 'required' : 'nullable', 'integer', Rule::exists('organisasis', 'id')],
            'nama' => [$isSuperAdmin ? 'required' : 'nullable', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:20', Rule::unique('cabors', 'kode')->ignore($id)],
            'kategori' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],

            // Legalitas & Dokumen Pengurus (Pengkot & Super Admin)
            'logo' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:5120'],
            'sk_nomor' => ['nullable', 'string', 'max:255'],
            'sk_tgl_mulai' => ['nullable', 'date'],
            'sk_tgl_selesai' => ['nullable', 'date'],
            'sk_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'nama_ketua' => ['nullable', 'string', 'max:255'],
            'kontak_pengurus' => ['nullable', 'string', 'max:255'],
        ];
    }
}
