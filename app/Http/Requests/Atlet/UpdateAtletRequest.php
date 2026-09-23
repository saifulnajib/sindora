<?php

namespace App\Http\Requests\Atlet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAtletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('atlet.manage') ?? false;
    }

    public function rules(): array
    {
        $atlet = $this->route('atlet');
        $id = is_object($atlet) ? $atlet->id : $atlet;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16', Rule::unique('atlets', 'nik')->ignore($id)->whereNull('deleted_at')],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'klub_id' => ['nullable', 'integer', Rule::exists('klubs', 'id')],
            'cabor_id' => ['required', 'integer', Rule::exists('cabors', 'id')],
            'pelatih_id' => ['nullable', 'integer', Rule::exists('sdms', 'id')],
            'kelurahan_id' => ['nullable', 'integer', Rule::exists('kelurahans', 'id')],
            'kelas_tanding' => ['nullable', 'string', 'max:100'],
            'status_pembinaan' => ['nullable', Rule::in(['daerah', 'provinsi', 'nasional'])],
            'berat_badan' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
        ];
    }
}
