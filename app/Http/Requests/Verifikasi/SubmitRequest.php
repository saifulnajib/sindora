<?php

namespace App\Http\Requests\Verifikasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        // Must have at least one manage permission or verifikasi capability
        return $user->can('klub.manage')
            || $user->can('atlet.manage')
            || $user->can('sdm.manage')
            || $user->can('sarpras.manage')
            || $user->can('kejuaraan.manage')
            || $user->can('prestasi.manage')
            || $user->can('pembinaan.manage')
            || $user->can('verifikasi.manage')
            || $user->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'entity_type' => ['required', 'string', Rule::in(['klub', 'atlet', 'sdm', 'sarpras', 'kejuaraan', 'prestasi', 'pembinaan'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'entity_type.in' => 'Entity type harus salah satu dari: klub, atlet, sdm, sarpras, kejuaraan, prestasi, pembinaan.',
            'ids.required' => 'Pilih minimal satu data untuk diajukan verifikasi.',
        ];
    }
}
