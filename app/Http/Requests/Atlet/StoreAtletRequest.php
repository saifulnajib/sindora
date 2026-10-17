<?php

namespace App\Http\Requests\Atlet;

use App\Models\Atlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAtletRequest extends FormRequest
{
    protected ?Atlet $existingAtlet = null;

    public function authorize(): bool
    {
        if (! ($this->user()?->can('atlet.manage') ?? false)) {
            return false;
        }

        if ($this->user()?->isPengkot()) {
            $assigned = $this->user()->getAssignedCaborIds();
            $cabors = $this->input('cabors', []);
            if (is_string($cabors)) {
                $cabors = json_decode($cabors, true) ?? [];
            }

            if (! empty($cabors)) {
                foreach ($cabors as $c) {
                    if (! empty($c['cabor_id']) && ! in_array((int) $c['cabor_id'], $assigned, true)) {
                        return false;
                    }
                }
            } elseif ($this->filled('cabor_id') && ! in_array((int) $this->input('cabor_id'), $assigned, true)) {
                return false;
            }
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('nik')) {
            $this->existingAtlet = Atlet::findByNik($this->input('nik'));
        }

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
        $isExisting = $this->existingAtlet !== null;

        return [
            'nama' => [$isExisting ? 'nullable' : 'required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16'],
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if existing athlete already has this cabor
            if ($this->existingAtlet) {
                $existingCaborIds = $this->existingAtlet->cabors()
                    ->pluck('cabors.id')
                    ->map(fn ($id) => (int) $id)
                    ->toArray();

                foreach ($this->input('cabors', []) as $idx => $c) {
                    if (! empty($c['cabor_id']) && in_array((int) $c['cabor_id'], $existingCaborIds, true)) {
                        $validator->errors()->add("cabors.{$idx}.cabor_id", 'Atlet ini sudah terdaftar di cabang olahraga tersebut.');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus 16 digit.',
            'cabors.required' => 'Pilih minimal satu cabang olahraga.',
            'cabors.min' => 'Pilih minimal satu cabang olahraga.',
            'cabors.*.cabor_id.required' => 'Cabang olahraga wajib dipilih.',
            'cabors.*.cabor_id.distinct' => 'Cabang olahraga tidak boleh duplikat.',
        ];
    }
}
