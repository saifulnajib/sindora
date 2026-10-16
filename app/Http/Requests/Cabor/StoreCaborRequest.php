<?php

namespace App\Http\Requests\Cabor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaborRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() ? $this->user()->can('create', \App\Models\Cabor::class) : false;
    }

    public function rules(): array
    {
        return [
            'organisasi_id' => ['required', 'integer', Rule::exists('organisasis', 'id')],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:20', 'unique:cabors,kode'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
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
