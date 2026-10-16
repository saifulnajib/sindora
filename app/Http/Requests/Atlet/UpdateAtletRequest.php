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

    protected function prepareForValidation(): void
    {
        if (is_string($this->cabors)) {
            $decoded = json_decode($this->cabors, true);
            if (is_array($decoded)) {
                $this->merge(['cabors' => $decoded]);
            }
        }

        if ((!$this->has('cabors') || empty($this->cabors)) && $this->filled('cabor_id')) {
            $this->merge([
                'cabors' => [
                    [
                        'cabor_id' => (int) $this->input('cabor_id'),
                        'klub_id' => $this->input('klub_id') ? (int) $this->input('klub_id') : null,
                        'pelatih_id' => $this->input('pelatih_id') ? (int) $this->input('pelatih_id') : null,
                        'kelas_tanding' => $this->input('kelas_tanding'),
                        'status_pembinaan' => $this->input('status_pembinaan') ?? 'daerah',
                    ],
                ],
            ]);
        }
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
            'kelurahan_id' => ['nullable', 'integer', Rule::exists('kelurahans', 'id')],
            'status_pembinaan' => ['nullable', Rule::in(['daerah', 'provinsi', 'nasional'])],
            'berat_badan' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            'cabors' => ['required', 'array', 'min:1'],
            'cabors.*.cabor_id' => ['required', 'integer', Rule::exists('cabors', 'id'), 'distinct'],
            'cabors.*.klub_id' => ['nullable', 'integer', Rule::exists('klubs', 'id')],
            'cabors.*.pelatih_id' => ['nullable', 'integer', Rule::exists('sdms', 'id')],
            'cabors.*.kelas_tanding' => ['nullable', 'string', 'max:100'],
            'cabors.*.status_pembinaan' => ['nullable', Rule::in(['daerah', 'provinsi', 'nasional'])],

            'cabor_id' => ['nullable', 'integer', Rule::exists('cabors', 'id')],
            'klub_id' => ['nullable', 'integer', Rule::exists('klubs', 'id')],
            'pelatih_id' => ['nullable', 'integer', Rule::exists('sdms', 'id')],
            'kelas_tanding' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'cabors.required' => 'Pilih minimal satu cabang olahraga.',
            'cabors.min' => 'Pilih minimal satu cabang olahraga.',
            'cabors.*.cabor_id.required' => 'Cabang olahraga wajib dipilih.',
            'cabors.*.cabor_id.distinct' => 'Cabang olahraga tidak boleh duplikat.',
        ];
    }
}
