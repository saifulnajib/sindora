<?php

namespace App\Http\Requests\Verifikasi;

use Illuminate\Foundation\Http\FormRequest;

class DecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        return $user->can('verifikasi.manage')
            || $user->hasRole('verifikator')
            || $user->hasRole('super_admin');
    }

    public function rules(): array
    {
        // For approve: catatan optional; for reject / request-revision: required min 10
        // We determine context via route name or explicit field; make flexible:
        // If this request is for reject or request-revision, catatan required.
        $routeName = $this->route()?->getName() ?? '';

        $isReject = str_contains($routeName, 'reject');
        $isRevision = str_contains($routeName, 'requestRevision') || str_contains($routeName, 'request-revision');

        if ($isReject || $isRevision) {
            return [
                'catatan_verifikator' => ['required', 'string', 'min:10', 'max:2000'],
            ];
        }

        // approve case
        return [
            'catatan_verifikator' => ['nullable', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan_verifikator.required' => 'Catatan verifikator wajib diisi (minimal 10 karakter).',
            'catatan_verifikator.min' => 'Catatan verifikator minimal 10 karakter.',
        ];
    }
}
